<?php declare(strict_types = 1);

use Symfony\Component\Console\Formatter\OutputFormatter;

require __DIR__.'/vendor/autoload.php';
require __DIR__.'/runner-config.php';

error_reporting(E_ALL);
ini_set('display_errors', '1');

$phpstanVersion = \Jean85\PrettyVersions::getVersion('phpstan/phpstan')->getPrettyVersion();

\Sentry\init([
	'dsn' => 'https://35e1e4a8936c4b70b8377056a5eeaeeb@sentry.io/1319523',
	'integrations' => [
		new \Sentry\Integration\ExceptionListenerIntegration(),
		new \Sentry\Integration\ErrorListenerIntegration(),
		new \Sentry\Integration\FatalErrorListenerIntegration(),
	]
]);

function clearTemp(): void
{
	// Bref's opcode cache is OPcache's *file* cache, which it points at /tmp - into a
	// directory named after OPcache's system id. Wiping /tmp wholesale therefore threw
	// away every compiled opcode on every single run, which is most of what an
	// invocation used to spend its time on.
	//
	// Kept, with the reasoning for why neither can carry anything between requests:
	//  - the OPcache system-id directory. opcache.validate_timestamps stays on, so a
	//    rewritten file is recompiled rather than served from a stale entry - which
	//    matters because PHPStan's own cache files are at fixed paths with per-request
	//    contents. The analysed snippet itself is parsed as data, never included.
	//  - the compiled DI containers, content-addressed on a hash of every config file
	//    plus the static parameters.
	//
	// Everything else still goes, including PHPStan's file cache and the previous
	// request's source file.
	$keep = ['/tmp/cache/nette.configurator'];
	foreach (glob('/tmp/*', GLOB_ONLYDIR) ?: [] as $dir) {
		if (preg_match('~^[0-9a-f]{32}~', basename($dir)) === 1) {
			$keep[] = $dir;
		}
	}

	$files = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator('/tmp', RecursiveDirectoryIterator::SKIP_DOTS),
		RecursiveIteratorIterator::CHILD_FIRST
	);

	foreach ($files as $fileinfo) {
		$path = $fileinfo->getPathname();
		foreach ($keep as $k) {
			if ($path === $k || str_starts_with($path, $k . '/') || str_starts_with($k, $path . '/')) {
				continue 2;
			}
		}
		$todo = ($fileinfo->isDir() && !$fileinfo->isLink() ? 'rmdir' : 'unlink');
		$todo($path);
	}
}

return function ($event) use ($phpstanVersion) {
	clearTemp();
	$code = $event['code'];
	$level = $event['level'];
	$codePath = '/tmp/tmp.php';
	file_put_contents($codePath, $code);

	$rootDir = getenv('LAMBDA_TASK_ROOT');
	if ($rootDir === false) {
		throw new RuntimeException('LAMBDA_TASK_ROOT is not set');
	}

	$configFiles = playground_config_files($rootDir, (bool) ($event['strictRules'] ?? false), (bool) ($event['bleedingEdge'] ?? false));
	$neon = playground_neon($configFiles, $event, $event['options'] ?? []);
	$finalConfigFile = playground_config_path($neon);
	file_put_contents($finalConfigFile, $neon);

	playground_seed_container($rootDir, playground_container_key((string) $level, $neon));

	require_once 'phar://' . $rootDir . '/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/ReflectionUnionType.php';
	require_once 'phar://' . $rootDir . '/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/ReflectionIntersectionType.php';
	require_once 'phar://' . $rootDir . '/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/ReflectionAttribute.php';
	require_once 'phar://' . $rootDir . '/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/Attribute85.php';
	require_once 'phar://' . $rootDir . '/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/Enum/UnitEnum.php';
	require_once 'phar://' . $rootDir . '/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/Enum/BackedEnum.php';
	require_once 'phar://' . $rootDir . '/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/Enum/ReflectionEnum.php';
	require_once 'phar://' . $rootDir . '/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/Enum/ReflectionEnumUnitCase.php';
	require_once 'phar://' . $rootDir . '/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/Enum/ReflectionEnumBackedCase.php';

	$containerFactory = new \PHPStan\DependencyInjection\ContainerFactory('/tmp');

	// ContainerFactory::postInitializeContainer() skips resetting PHPStan's global state
	// (BetterReflection, the reflection provider and PHP version accessors, ObjectType and
	// TypeCombinator caches, the feature toggles) when the new container happens to get the
	// same spl_object_id as the previous one. With BREF_LOOP_MAX above 1 several containers
	// are built in one process, so that is no longer impossible - and it would fail silently,
	// analysing the request against the previous request's PHP version. Forgetting the last
	// id makes the reset unconditional; it measured at ~7 ms.
	$lastContainerId = new ReflectionProperty(\PHPStan\DependencyInjection\ContainerFactory::class, 'lastInitializedContainerId');
	$lastContainerId->setAccessible(true);
	$lastContainerId->setValue(null, null);

	$container = $containerFactory->create(
		'/tmp',
		[sprintf('%s/config.level%s.neon', $containerFactory->getConfigDirectory(), $level), $finalConfigFile],
		[$codePath],
		additionalParameters: playground_extra_parameters(),
	);

	/** @var \PHPStan\Analyser\Analyser $analyser */
	$analyser = $container->getByType(\PHPStan\Analyser\Analyser::class);
	$analyserResult = $analyser->analyse([$codePath], null, null, false, [$codePath]);

	/** @var \PHPStan\Analyser\AnalyserResultFinalizer $analyserResultFinalizer */
	$analyserResultFinalizer = $container->getByType(\PHPStan\Analyser\AnalyserResultFinalizer::class);
	$analyserResult = $analyserResultFinalizer->finalize($analyserResult, true, false);
	$results = $analyserResult->getErrors();

	error_clear_last();

	$errors = [];
	$tipFormatter = new OutputFormatter(false);
    $diffs = [];
	foreach ($results as $result) {
		$error = [
			'message' => $result->getMessage(),
			'line' => $result->getLine(),
			'ignorable' => $result->canBeIgnored(),
		];
		if ($result->getTip() !== null) {
			$error['tip'] = $tipFormatter->format($result->getTip());
		}
		if ($result->getIdentifier() !== null) {
			$error['identifier'] = $result->getIdentifier();
		}
        if ($result->getFixedErrorDiff() !== null) {
            $diffs[] = $result->getFixedErrorDiff();
            $error['fixDiff'] = $result->getFixedErrorDiff()->diff;
        }
		$errors[] = $error;
	}

    $response = ['result' => $errors, 'version' => $phpstanVersion];

    if (count($diffs) > 0) {
        /** @var \PHPStan\Fixable\Patcher $patcher */
        $patcher = $container->getByType(\PHPStan\Fixable\Patcher::class);
        $differ = new \SebastianBergmann\Diff\Differ(new \SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder('', addLineNumbers: true));
        $fixedCode = $patcher->applyDiffs($codePath, $diffs);
        $response['fixedCode'] = $fixedCode;
        $response['fixedCodeDiff'] = $differ->diff($code, $fixedCode);
    }

	return $response;
};
