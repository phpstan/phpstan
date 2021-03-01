<?php declare(strict_types = 1);

require 'phar://' .  __DIR__ . '/vendor/phpstan/phpstan/phpstan.phar/vendor/composer/InstalledVersions.php';

echo \Composer\InstalledVersions::getReference('phpstan/phpstan-src');
