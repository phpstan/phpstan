#!/usr/bin/env bash

set -o errexit
set -o pipefail
set -o nounset

git clone $1 extension
cd extension
composer install --ignore-platform-reqs
cp ../phpstan.phar vendor/phpstan/phpstan/phpstan.phar
cp ../phpstan.phar vendor/phpstan/phpstan/phpstan
cp ../bootstrap.php vendor/phpstan/phpstan/bootstrap.php

if [[ "$STATIC_REFLECTION" == "true" ]]; then
  echo "Running with static reflection"
  cp ../e2e/bootstrap-static-reflection.php tests/bootstrap.php
fi

vendor/bin/phing
