#!/usr/bin/env bash

set -o errexit
set -o pipefail
set -o nounset

IFS=" " read -r -a config <<< "${1//:/ }"

git clone "https://github.com/phpstan/${config[0]}.git" extension
cd extension

if [[ ${#config[@]} -eq 2 ]]; then
  git checkout "${config[1]}"
fi;

if [[ "$PHP_VERSION" == "7.2" ]]; then
  if [[ "$1" == "phpstan-mockery" ]]; then
    composer require --dev phpunit/phpunit:'^8.5.31' mockery/mockery:^1.3 --update-with-dependencies
  else
    composer require --dev phpunit/phpunit:'^8.5.31' --update-with-dependencies
  fi;

  composer install --no-interaction
else
  composer install --no-interaction
fi;

cp ../phpstan.phar vendor/phpstan/phpstan/phpstan.phar
cp ../phpstan vendor/phpstan/phpstan/phpstan
cp ../bootstrap.php vendor/phpstan/phpstan/bootstrap.php

make tests
make phpstan
