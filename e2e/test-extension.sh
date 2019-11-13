#!/bin/sh

set -o errexit
set -o pipefail
set -o nounset

git clone $1 extension
cd extension
composer install
cp ../phpstan.phar vendor/phpstan/phpstan/phpstan.phar
cp ../phpstan.phar vendor/phpstan/phpstan/phpstan
vendor/bin/phing
