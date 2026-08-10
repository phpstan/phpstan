#!/bin/sh
# Rebuilds the official php:*-cli-alpine binary with profile-guided
# optimization, trained by running the shipped PHPStan release on the
# phpstan-src codebase (measured: ~5-10% faster analysis). The sources,
# configure options and compiler flags all come from the image itself, so
# the result differs from the stock binary only in the profile-guided code
# layout.
#
# Meant to run inside a pgo-builder stage (see the Dockerfiles):
#   pgo-build instrument   compile the bundled PHP sources with profiling
#   pgo-build train        run PHPStan on phpstan-src, gathering the profile
#                          (needs PHPSTAN_VERSION and PHPSTAN_SRC_REF)
#   pgo-build rebuild      recompile with the profile, install to /pgo-install
set -eux

export COMPOSER_HOME=/composer COMPOSER_ALLOW_SUPERUSER=1

php_build_flags() {
	# -Wno-error=incompatible-pointer-types: PHP <= 8.1 sources predate GCC 14
	# turning this warning into an error (musl fopencookie seeker signature);
	# the stock images were built back when it was a warning.
	export CFLAGS="$PHP_CFLAGS -Wno-error=incompatible-pointer-types" CPPFLAGS="$PHP_CPPFLAGS" LDFLAGS="$PHP_LDFLAGS"
}

case "$1" in

instrument)
	# This stage is discarded, no need to keep the apk cache clean - and the
	# index must be around for the --simulate availability probes below.
	apk update
	apk add git
	# Union of docker-library/php's own build dependency lists across the
	# supported PHP/Alpine versions; packages a given Alpine does not have
	# are filtered out.
	candidates="$PHPIZE_DEPS argon2-dev coreutils curl-dev gnu-libiconv-dev \
		libedit-dev libsodium-dev libxml2-dev linux-headers oniguruma-dev \
		openssl-dev readline-dev sqlite-dev"
	deps=""
	for p in $candidates; do
		if apk add --simulate "$p" > /dev/null 2>&1; then deps="$deps $p"; fi
	done
	echo "build deps: $deps"
	test -n "$deps"
	# shellcheck disable=SC2086
	apk add --virtual .build-deps $deps
	command -v gcc
	# make sure musl's iconv doesn't get used, same as the official build
	rm -vf /usr/include/iconv.h
	docker-php-source extract
	cd /usr/src/php
	php_build_flags
	# php-config drops the quoting around PHP_UNAME='Linux - Docker'; restore it
	configureOptions="$(php-config --configure-options | sed "s/PHP_UNAME=Linux - Docker/PHP_UNAME='Linux - Docker'/")"
	eval ./configure "$configureOptions"
	make -j"$(nproc)" PROF_FLAGS="-fprofile-generate -fprofile-update=atomic" all
	;;

train)
	: "${PHPSTAN_VERSION:?}" "${PHPSTAN_SRC_REF:?}"
	composer global require phpstan/phpstan:"$PHPSTAN_VERSION" --prefer-dist
	composer clear-cache
	git clone --depth 1 --branch "$PHPSTAN_SRC_REF" https://github.com/phpstan/phpstan-src.git /pgo-corpus
	cd /pgo-corpus
	# phpstan-src's autoload.files (debugScope.php etc.) collide with the
	# phar's own copies when the phar loads the corpus autoloader - the
	# function files stay in src/ for analysis, they just must not be loaded
	php -r '$j = json_decode(file_get_contents("composer.json"), true); unset($j["autoload"]["files"]); file_put_contents("composer.json", json_encode($j));'
	composer install --no-dev --no-scripts --no-plugins --ignore-platform-reqs --no-interaction
	printf 'parameters:\n    level: 8\n    paths:\n        - src\n' > pgo-training.neon
	# Found errors are expected (exit code 1) - the run exercising the
	# engine is the point. Anything else is a broken training run and must
	# fail the build, as must an empty profile. Parallel worker processes
	# merge their profiles safely thanks to -fprofile-update=atomic.
	/usr/src/php/sapi/cli/php -d memory_limit=-1 /composer/vendor/bin/phpstan analyse \
		-c pgo-training.neon --no-progress -q || test "$?" -eq 1
	gcdaCount="$(find /usr/src/php -name '*.gcda' | wc -l)"
	echo "PGO training produced $gcdaCount profile data files"
	test "$gcdaCount" -gt 100
	;;

rebuild)
	cd /usr/src/php
	make prof-clean
	php_build_flags
	# -fprofile-partial-training keeps GCC from pessimizing code the
	# training did not reach (untrained != cold)
	make -j"$(nproc)" PROF_FLAGS="-fprofile-use -fprofile-correction -fprofile-partial-training -Wno-missing-profile -Wno-coverage-mismatch" all
	make INSTALL_ROOT=/pgo-install install-cli
	/pgo-install/usr/local/bin/php -v
	;;

*)
	echo "usage: pgo-build instrument|train|rebuild" >&2
	exit 1
	;;

esac
