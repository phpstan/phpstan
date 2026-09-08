<?php declare(strict_types = 1);

/**
 * Builds the PHPStan configuration for one playground request.
 *
 * bref.php and precompile.php both go through here so that the generated NEON -
 * and therefore the DI container cache key - is byte for byte identical.
 */

/**
 * @return list<string>
 */
function playground_config_files(string $rootDir, bool $strictRules, bool $bleedingEdge): array
{
	$configFiles = [
		$rootDir . '/playground.neon',
		$rootDir . '/vendor/phpstan/phpstan-deprecation-rules/rules.neon',
	];
	if ($strictRules) {
		$configFiles[] = $rootDir . '/vendor/phpstan/phpstan-strict-rules/rules.neon';
	}
	if ($bleedingEdge) {
		$configFiles[] = 'phar://' . $rootDir . '/vendor/phpstan/phpstan/phpstan.phar/conf/bleedingEdge.neon';
	}

	return $configFiles;
}

/**
 * @param list<string> $configFiles
 * @param array{treatPhpDocTypesAsCertain?: bool, phpVersion?: int} $event
 * @param array<string, mixed> $options
 */
function playground_neon(array $configFiles, array $event, array $options): string
{
	$parameters = [
		'inferPrivatePropertyTypeFromConstructor' => $options['inferPrivatePropertyTypeFromConstructor'] ?? true,
		'treatPhpDocTypesAsCertain' => $event['treatPhpDocTypesAsCertain'] ?? true,
		'phpVersion' => $event['phpVersion'] ?? 80000,
		'sourceLocatorPlaygroundMode' => true,
		'rememberPossiblyImpureFunctionValues' => $options['rememberPossiblyImpureFunctionValues'] ?? true,
		'checkBenevolentUnionTypes' => $options['checkBenevolentUnionTypes'] ?? false,
		'checkTooWideReturnTypesInProtectedAndPublicMethods' => $options['checkTooWideTypesInProtectedAndPublicMethods'] ?? false,
		'checkTooWideParameterOutInProtectedAndPublicMethods' => $options['checkTooWideTypesInProtectedAndPublicMethods'] ?? false,
		'checkTooWideThrowTypesInProtectedAndPublicMethods' => $options['checkTooWideTypesInProtectedAndPublicMethods'] ?? false,
		'reportUnsafeArrayStringKeyCasting' => $options['reportUnsafeArrayStringKeyCasting'] ?? null,
	];

	$parameters['exceptions'] = [
		'implicitThrows' => $options['implicitThrows'] ?? true,
		'reportUncheckedExceptionDeadCatch' => $options['reportUncheckedExceptionDeadCatch'] ?? true,
		'uncheckedExceptionClasses' => $options['uncheckedExceptionClasses'] ?? [],
		'checkedExceptionClasses' => $options['checkedExceptionClasses'] ?? [],
		'check' => [
			'missingCheckedExceptionInThrows' => $options['missingCheckedExceptionInThrows'] ?? false,
			'tooWideImplicitThrowType' => $options['tooWideImplicitThrowType'] ?? false,
		],
	];

	return \Nette\Neon\Neon::encode([
		'includes' => $configFiles,
		'parameters' => $parameters,
		'services' => [
			'currentPhpVersionSimpleParser!' => [
				'factory' => '@currentPhpVersionRichParser',
			],
		],
	]);
}

/**
 * Parameters handed to ContainerFactory::create() on top of its own defaults.
 *
 * @return array<string, mixed>
 */
function playground_extra_parameters(): array
{
	return [
		// %env% is only ever read as part of the DI container cache key - nothing in
		// PHPStan's configs or sources uses it. On Lambda it holds _X_AMZN_TRACE_ID,
		// which differs on every single invocation, so leaving it in makes the compiled
		// container impossible to reuse. It also bakes the function's AWS credentials
		// into the generated container file in /tmp.
		'env' => [],

		// ContainerFactory hardcodes setDebugMode(true), which turns on Nette's
		// autoRebuild: before reusing a cached container it reflects over every service
		// class to check whether it went stale. The cache key already covers the hash of
		// every config file, and PHPStan's own sources cannot change inside a running
		// Lambda execution environment, so that check is pure overhead here.
		'debugMode' => false,
		'productionMode' => true,
	];
}

/**
 * Where to write the generated config for a request.
 *
 * The path is derived from the config itself rather than being a fixed
 * /tmp/run-phpstan-tmp.neon, because PHPStan caches parsed NEON keyed by file path.
 * With one shared path, a warm execution environment can answer a request with the
 * *previous* request's parsed configuration - so a snippet gets analysed against
 * someone else's PHP version or rule set. Hashing the contents into the name makes
 * that impossible while keeping the path deterministic, which is what lets the
 * compiled DI container be cached and precompiled at all.
 */
function playground_config_path(string $neon): string
{
	return '/tmp/run-phpstan-' . substr(hash('sha256', $neon), 0, 32) . '.neon';
}

/**
 * Identifies the DI container a request needs.
 *
 * The container cache key is a hash of every config file plus the static parameters.
 * Inside a given deployment package all of those are fixed except the level (which
 * picks config.levelN.neon) and the generated NEON, so hashing just those two names
 * the container - without having to reimplement Nette's key building.
 */
function playground_container_key(string $level, string $neon): string
{
	return hash('sha256', $level . "\0" . $neon);
}

/**
 * Unpacks the precompiled DI container for this request, if one was shipped.
 *
 * A fresh execution environment would otherwise spend ~1.5 s compiling the container
 * before it could analyse anything - and with nine PHP versions fanned out over
 * separate environments, that cost lands on most requests. The containers are
 * content-addressed and contain no trace of any analysed code.
 *
 * @return string one of: hit (already in /tmp), unpacked, absent
 */
function playground_seed_container(string $rootDir, string $key): string
{
	static $index = null;
	if ($index === null) {
		$indexFile = $rootDir . '/precompiled-containers/index.php';
		$index = is_file($indexFile) ? require $indexFile : [];
	}

	if (!isset($index[$key])) {
		return 'absent';
	}

	$target = '/tmp/cache/nette.configurator/' . $index[$key] . '.php';
	if (is_file($target)) {
		return 'hit';
	}

	$packed = $rootDir . '/precompiled-containers/' . $index[$key] . '.php.gz';
	$contents = is_file($packed) ? @gzdecode((string) file_get_contents($packed)) : false;
	if ($contents === false) {
		return 'absent';
	}

	$dir = dirname($target);
	if (!is_dir($dir) && !@mkdir($dir, 0777, true) && !is_dir($dir)) {
		return 'absent';
	}

	// Write then rename so a half-written file can never be included.
	$tmp = $target . '.' . getmypid() . '.tmp';
	if (@file_put_contents($tmp, $contents) !== strlen($contents) || !@rename($tmp, $target)) {
		@unlink($tmp);
		return 'absent';
	}

	return 'unpacked';
}
