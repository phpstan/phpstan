<?php declare(strict_types = 1);

use Symfony\Component\Console\Formatter\OutputFormatter;

// bin/phpstan does the same: PHPStan allocates heavily and leans on the process
// ending rather than on the cycle collector.
gc_disable();

require __DIR__.'/vendor/autoload.php';
require __DIR__.'/runner-config.php';
require __DIR__.'/xray.php';

error_reporting(E_ALL);
ini_set('display_errors', '1');

// The same limit as `phpstan analyse --fix`.
const PLAYGROUND_FIX_ROUNDS_LIMIT = 10;

$phpstanVersion = \Jean85\PrettyVersions::getVersion('phpstan/phpstan')->getPrettyVersion();

\Honeybadger\Honeybadger::new([
	'api_key' => getenv('HONEYBADGER_API_KEY') ?: null,
	// the default rethrows when Honeybadger can't be reached, failing the request
	'service_exception_handler' => static function (): void {
	},
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
	$code = $event['code'] ?? null;
	if (!is_string($code)) {
		throw new InvalidArgumentException('Missing or invalid "code" in the request.');
	}
	// the level picks a config file by name, so only accept the levels PHPStan ships
	$level = $event['level'] ?? null;
	if (is_int($level)) {
		$level = (string) $level;
	}
	if (!is_string($level) || preg_match('~^(?:[0-9]|10|max)$~', $level) !== 1) {
		throw new InvalidArgumentException('Missing or invalid "level" in the request.');
	}

	clearTemp();
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

	$analyse = static function () use ($level, $finalConfigFile, $codePath): array {
		$containerFactory = new \PHPStan\DependencyInjection\ContainerFactory('/tmp');
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

		/** @var \PHPStan\Fixable\ErrorFixer $errorFixer */
		$errorFixer = $container->getByType(\PHPStan\Fixable\ErrorFixer::class);

		return [$analyserResultFinalizer->finalize($analyserResult, true, false), $errorFixer];
	};

	[$analyserResult, $errorFixer] = $analyse();
	$results = $analyserResult->getErrors();

	error_clear_last();

	$errors = [];
	$tipFormatter = new OutputFormatter(false);
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
		if ($result->isFixable()) {
			$error['fixable'] = true;
		}
		$errors[] = $error;
	}

	$response = [
		'result' => $errors,
		'version' => $phpstanVersion,
		'xray' => playground_xray($code, $analyserResult->getAnalyserResult()->getCollectedData()[$codePath] ?? []),
	];

	// Like `phpstan analyse --fix`: the fixed code is analysed again in a new container
	// and fixed again, which applies fixes skipped because they overlapped with other fixes
	// and fixes errors caused by the previous round.
	$roundResults = $results;
	for ($round = 1; ; $round++) {
		$fixedCount = count($errorFixer->fix($roundResults)->getFixedErrors());
		if ($fixedCount === 0 || $round === PLAYGROUND_FIX_ROUNDS_LIMIT) {
			break;
		}
		[$roundAnalyserResult, $errorFixer] = $analyse();
		$roundResults = $roundAnalyserResult->getErrors();
	}

	$fixedCode = (string) file_get_contents($codePath);
	if ($fixedCode !== $code) {
		$differ = new \SebastianBergmann\Diff\Differ(new \SebastianBergmann\Diff\Output\StrictUnifiedDiffOutputBuilder([
			'header' => '',
			'addLineNumbers' => true,
			'emitNoLineEndEofWarning' => false,
		]));
		$response['fixedCode'] = $fixedCode;
		$response['fixedCodeDiff'] = $differ->diff($code, $fixedCode);
	}

	return $response;
};
