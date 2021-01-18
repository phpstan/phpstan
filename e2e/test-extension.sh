#!/usr/bin/env bash

set -o errexit
set -o pipefail
set -o nounset

git clone $1 extension
cd extension

if [[ "$PHP_VERSION" == "8.0" ]]; then
  composer install --ignore-platform-reqs
  composer require --dev phpunit/phpunit:'^9.3' --update-with-dependencies --ignore-platform-reqs
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

vendor/bin/phing tests
vendor/bin/phing phpstan
