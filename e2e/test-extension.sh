#!/usr/bin/env bash

set -o errexit
set -o pipefail
set -o nounset

git clone $1 extension
cd extension
composer install
cp ../phpstan.phar vendor/phpstan/phpstan/phpstan.phar
cp ../phpstan.phar vendor/phpstan/phpstan/phpstan
cp ../bootstrap.php vendor/phpstan/phpstan/bootstrap.php
vendor/bin/phing
