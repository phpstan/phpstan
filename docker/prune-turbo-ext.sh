#!/bin/sh
# The phpstan/phpstan package ships turbo extension binaries for every
# platform and PHP version - keep only the one
# PHPStan\Turbo\TurboExtensionSelector picks for this image (and the shared
# core next to it), or none on PHP < 8.3.
#
# Bind-mounted into the RUN that installs PHPStan (see the Dockerfiles), so
# the deleted files never make it into an image layer, and neither does this
# script.
set -eu

cd /composer/vendor/phpstan/phpstan/turbo-ext

platform="linux-musl-$(uname -m | sed 's/aarch64/arm64/')"
extension="$(php -r 'printf("phpstan_turbo-%d.%d%s.so", PHP_MAJOR_VERSION, PHP_MINOR_VERSION, PHP_ZTS ? "-zts" : "");')"

if [ -f "$platform/$extension" ]; then
	find . -type f ! -path "./$platform/$extension" ! -path "./$platform/phpstan_turbo_core.so" -delete
elif php -r 'exit(PHP_VERSION_ID >= 80300 ? 0 : 1);'; then
	echo "turbo-ext/$platform/$extension not found" >&2
	exit 1
else
	find . -type f -delete
fi

find . -mindepth 1 -type d -empty -delete
