<?php declare(strict_types = 1);

spl_autoload_register(function (string $class): void {
	if (!extension_loaded('phar') || defined('__PHPSTAN_RUNNING__')) {
		return;
	}

	if (strpos($class, '_HumbugBox') === 0) {
		if (!in_array('phar', stream_get_wrappers(), true)) {
			throw new \Exception('Phar wrapper is not registered. Please review your php.ini settings.');
		}

		$composerAutoloader = require 'phar://' . __DIR__ . '/phpstan.phar/vendor/autoload.php';
		$composerAutoloader->loadClass($class);

		return;
	}
	if (strpos($class, 'PHPStan\\PhpDocParser\\') === 0) {
		return;
	}
	if (strpos($class, 'PHPStan\\') !== 0) {
		return;
	}

	if (!in_array('phar', stream_get_wrappers(), true)) {
		throw new \Exception('Phar wrapper is not registered. Please review your php.ini settings.');
	}

	$filename = str_replace('\\', DIRECTORY_SEPARATOR, $class);
	$filename = substr($filename, strlen('PHPStan\\'));
	$filepath = 'phar://' . __DIR__ . '/phpstan.phar/src/' . $filename . '.php';
	if (!file_exists($filepath)) {
		return;
	}

	require $filepath;
});
