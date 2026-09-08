<?php declare(strict_types = 1);

/**
 * Compiles PHPStan DI containers for a slice of the playground's configuration
 * matrix and returns them base64-encoded, so the deploy pipeline can ship them
 * inside the Lambda package.
 *
 * It has to run inside Lambda because the container cache key contains the
 * absolute paths of the package (/var/task/...) and of the temp directory.
 */

require __DIR__ . '/vendor/autoload.php';

require_once 'phar://' . __DIR__ . '/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/ReflectionUnionType.php';
require_once 'phar://' . __DIR__ . '/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/ReflectionIntersectionType.php';
require_once 'phar://' . __DIR__ . '/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/ReflectionAttribute.php';
require_once 'phar://' . __DIR__ . '/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/Attribute85.php';
require_once 'phar://' . __DIR__ . '/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/Enum/UnitEnum.php';
require_once 'phar://' . __DIR__ . '/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/Enum/BackedEnum.php';
require_once 'phar://' . __DIR__ . '/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/Enum/ReflectionEnum.php';
require_once 'phar://' . __DIR__ . '/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/Enum/ReflectionEnumUnitCase.php';
require_once 'phar://' . __DIR__ . '/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/Enum/ReflectionEnumBackedCase.php';

require __DIR__ . '/runner-config.php';

function clear_dir(string $dir): void
{
	if (!is_dir($dir)) {
		return;
	}
	$files = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
		RecursiveIteratorIterator::CHILD_FIRST
	);
	foreach ($files as $f) {
		$f->isDir() ? rmdir($f->getPathname()) : unlink($f->getPathname());
	}
}

function clear_phpstan_cache(): void
{
	clear_dir('/tmp/cache/PHPStan');
}

return function (array $event): array {
	/** @var list<array{level: string, phpVersion: int, strictRules: bool, bleedingEdge: bool, treatPhpDocTypesAsCertain: bool}> $combos */
	$combos = $event['combos'];
	$cacheDir = '/tmp/cache/nette.configurator';

	$rootDir = getenv('LAMBDA_TASK_ROOT');
	if ($rootDir === false) {
		return ['error' => 'LAMBDA_TASK_ROOT is not set', 'log' => []];
	}

	$files = [];
	$index = [];
	$log = [];
	foreach ($combos as $combo) {
		$start = microtime(true);

		// PHPStan caches the parsed NEON keyed by file path, and every combo reuses the
		// same /tmp/run-phpstan-tmp.neon. Without dropping that cache first, a combo
		// would be compiled from the *previous* combo's parameters and stored under its
		// own (correct) name - which is exactly the kind of cross-request bleed the
		// handler's clearTemp() exists to prevent.
		// Start from an empty container directory so the one container left behind is
		// unambiguously this combo's, whatever the execution environment saw before.
		clear_dir($cacheDir);
		clear_phpstan_cache();

		$configFiles = playground_config_files($rootDir, $combo['strictRules'], $combo['bleedingEdge']);
		$neon = playground_neon($configFiles, [
			'treatPhpDocTypesAsCertain' => $combo['treatPhpDocTypesAsCertain'],
			'phpVersion' => $combo['phpVersion'],
		], []);
		$configPath = playground_config_path($neon);
		file_put_contents($configPath, $neon);
		$key = playground_container_key($combo['level'], $neon);

		$containerFactory = new \PHPStan\DependencyInjection\ContainerFactory('/tmp');
		$containerFactory->create(
			'/tmp',
			[sprintf('%s/config.level%s.neon', $containerFactory->getConfigDirectory(), $combo['level']), $configPath],
			['/tmp/tmp.php'],
			additionalParameters: playground_extra_parameters(),
		);

		$compiled = glob($cacheDir . '/Container_*.php') ?: [];
		if (count($compiled) !== 1) {
			return ['error' => sprintf('expected exactly one container for %s, got %d', json_encode($combo), count($compiled)), 'log' => $log];
		}

		$contents = file_get_contents($compiled[0]);
		$packed = $contents === false ? false : gzencode($contents, 6);
		if ($packed === false) {
			return ['error' => sprintf('could not read back the container for %s', json_encode($combo)), 'log' => $log];
		}

		$name = basename($compiled[0], '.php');
		$index[$key] = $name;
		$files[$name] = base64_encode($packed);
		$log[] = sprintf('%s/%d/s%d/b%d/t%d -> %s (%.0f ms)', $combo['level'], $combo['phpVersion'],
			(int) $combo['strictRules'], (int) $combo['bleedingEdge'], (int) $combo['treatPhpDocTypesAsCertain'],
			$name, (microtime(true) - $start) * 1000);
	}

	return ['files' => $files, 'index' => $index, 'log' => $log];
};
