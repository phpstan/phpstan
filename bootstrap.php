<?php declare(strict_types = 1);

namespace PHPStan;

use Composer\Autoload\ClassLoader;

final class PharAutoloader
{
	/** @var ClassLoader */
	private static $composerAutoloader;

	final public static function loadClass(string $class): void {
		if (!extension_loaded('phar') || defined('__PHPSTAN_RUNNING__')) {
			return;
		}

		if (strpos($class, '_HumbugBox') === 0) {
			if (!in_array('phar', stream_get_wrappers(), true)) {
				throw new \Exception('Phar wrapper is not registered. Please review your php.ini settings.');
			}

			if (self::$composerAutoloader === null) {
				self::$composerAutoloader = require 'phar://' . __DIR__ . '/phpstan.phar/vendor/autoload.php';
				require_once 'phar://' . __DIR__ . '/phpstan.phar/vendor/clue/block-react/src/functions_include.php';
				require_once 'phar://' . __DIR__ . '/phpstan.phar/vendor/jetbrains/phpstorm-stubs/PhpStormStubsMap.php';
				require_once 'phar://' . __DIR__ . '/phpstan.phar/vendor/react/promise-stream/src/functions_include.php';
				require_once 'phar://' . __DIR__ . '/phpstan.phar/vendor/react/promise-timer/src/functions_include.php';
				require_once 'phar://' . __DIR__ . '/phpstan.phar/vendor/react/promise/src/functions_include.php';
				require_once 'phar://' . __DIR__ . '/phpstan.phar/vendor/ringcentral/psr7/src/functions_include.php';
			}
			self::$composerAutoloader->loadClass($class);

			return;
		}
		if (strpos($class, 'PHPStan\\') !== 0 || strpos($class, 'PHPStan\\PhpDocParser\\') === 0) {
			return;
		}

		if (!in_array('phar', stream_get_wrappers(), true)) {
			throw new \Exception('Phar wrapper is not registered. Please review your php.ini settings.');
		}

		$filename = str_replace('\\', DIRECTORY_SEPARATOR, $class);
		if (strpos($class, 'PHPStan\\BetterReflection\\') === 0) {
			$filename = substr($filename, strlen('PHPStan\\BetterReflection\\'));
			$filepath = 'phar://' . __DIR__ . '/phpstan.phar/vendor/ondrejmirtes/better-reflection/src/' . $filename . '.php';
		} else {
			$filename = substr($filename, strlen('PHPStan\\'));
			$filepath = 'phar://' . __DIR__ . '/phpstan.phar/src/' . $filename . '.php';
		}

		if (!file_exists($filepath)) {
			return;
		}

		require $filepath;
	}
}

spl_autoload_register([PharAutoloader::class, 'loadClass']);
