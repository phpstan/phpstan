#!/usr/bin/env bash

set -o errexit
set -o pipefail
set -o nounset

git clone $1 extension
cd extension

if [[ "$PHP_VERSION" == "7.1" || "$PHP_VERSION" == "7.2" ]]; then
  composer install --no-interaction --no-suggest
  composer require --dev phpunit/phpunit:'^7.5.20' --update-with-dependencies
else
  composer install
fi;

cp ../phpstan.phar vendor/phpstan/phpstan/phpstan.phar
cp ../phpstan vendor/phpstan/phpstan/phpstan
cp ../bootstrap.php vendor/phpstan/phpstan/bootstrap.php

if [[ "$STATIC_REFLECTION" == "true" ]]; then
  echo "Running with static reflection"
  cp ../e2e/bootstrap-static-reflection.php tests/bootstrap.php
fi

make tests
make phpstan
