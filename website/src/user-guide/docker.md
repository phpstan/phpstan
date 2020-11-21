---
title: Docker
---

The image is based on [Alpine Linux](https://alpinelinux.org/) and updated automatically.

It's hosted on [GitHub Container Registry](https://github.com/orgs/phpstan/packages/container/package/phpstan).

## Supported tags

- `0.12`, `latest`
- `nightly` (dev-master)

## Installation

```yaml
docker pull ghcr.io/phpstan/phpstan
```

Alternatively, pull a specific version:

```bash
docker pull ghcr.io/phpstan/phpstan:0.12
```

## Different PHP version?

The image is based on PHP 8. To force PHPStan consider the analysed source code to be for a different PHP version, set `phpVersion` in your `phpstan.neon`:

```yaml
parameters:
    phpVersion: 70400 # PHP 7.4
```

## Usage

We recommend to use the images as a shell alias shortcut.

To use `phpstan` everywhere  in the CLI add this line to your `~/.zshrc`, `~/.bashrc`, or `~/.profile`.

```bash
alias phpstan='docker run -v $PWD:/app --rm ghcr.io/phpstan/phpstan'
```

If you don't have set the alias, use this command to run the container:

```bash
docker run --rm -v /path/to/app:/app ghcr.io/phpstan/phpstan [some arguments for PHPStan]
```

For example:

```bash
docker run --rm -v /path/to/app:/app ghcr.io/phpstan/phpstan analyse /app/src
```

## Install PHPStan extensions

If you need a PHPStan extension, for example [phpstan/phpstan-phpunit](https://github.com/phpstan/phpstan-phpunit), you can simply
extend an existing image and add the relevant extension via Composer.
In some cases you also need some additional PHP extensions like DOM. (see section below)

Here is an example Dockerfile for `phpstan/phpstan-phpunit`:

```docker
FROM ghcr.io/phpstan/phpstan:latest
RUN composer global require phpstan/phpstan-phpunit
```

You can update the `phpstan.neon` file in order to use the extension:

```yaml
includes:
	- /composer/vendor/phpstan/phpstan-phpunit/extension.neon
```

## Install PHP extensions

Sometimes your codebase requires some additional PHP extensions like `intl` or maybe `soap`.

Therefore you need to know that our Docker image extends the [official php:cli-alpine Docker image](https://hub.docker.com/_/php).
So only the default built-in extensions are available (see below).

To solve this issue you can extend our Docker image in a custom Dockerfile like this, for example to add `soap` and `intl`:

```docker
FROM ghcr.io/phpstan/phpstan:latest
RUN apk --update --progress --no-cache add icu-dev libxml2-dev \
	&& docker-php-ext-install intl soap
```

## Default built-in PHP extensions

You can use the following command to determine which PHP extensions are already installed in the base image:

```bash
docker run --rm php:cli-alpine -m
```

This should give you an output like this:

```ini
[PHP Modules]
Core
ctype
curl
date
dom
fileinfo
filter
ftp
hash
iconv
json
libxml
mbstring
mysqlnd
openssl
pcre
PDO
pdo_sqlite
Phar
posix
readline
Reflection
session
SimpleXML
sodium
SPL
sqlite3
standard
tokenizer
xml
xmlreader
xmlwriter
zlib

[Zend Modules]
```
