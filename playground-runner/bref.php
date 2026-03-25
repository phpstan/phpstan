<?php declare(strict_types = 1);

use Symfony\Component\Console\Formatter\OutputFormatter;

require __DIR__.'/vendor/autoload.php';

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
	$files = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator('/tmp', RecursiveDirectoryIterator::SKIP_DOTS),
		RecursiveIteratorIterator::CHILD_FIRST
	);

	foreach ($files as $fileinfo) {
		$todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
		$todo($fileinfo->getRealPath());
	}
}

return function ($event) use ($phpstanVersion) {
	clearTemp();
	$code = $event['code'];
	$level = $event['level'];
	$codePath = '/tmp/tmp.php';
	file_put_contents($codePath, $code);

	$rootDir = getenv('LAMBDA_TASK_ROOT');
	$configFiles = [
		$rootDir . '/playground.neon',
        $rootDir . '/vendor/phpstan/phpstan-deprecation-rules/rules.neon',
	];
	foreach ([
		'strictRules' => $rootDir . '/vendor/phpstan/phpstan-strict-rules/rules.neon',
		'bleedingEdge' => 'phar://' . $rootDir . '/vendor/phpstan/phpstan/phpstan.phar/conf/bleedingEdge.neon',
	] as $key => $file) {
		if (!isset($event[$key]) || !$event[$key]) {
			continue;
		}

		$configFiles[] = $file;
	}

	$options = $event['options'] ?? [];

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

	$finalConfigFile = '/tmp/run-phpstan-tmp.neon';
	$neon = \Nette\Neon\Neon::encode([
		'includes' => $configFiles,
		'parameters' => $parameters,
		'services' => [
			'currentPhpVersionSimpleParser!' => [
				'factory' => '@currentPhpVersionRichParser',
			],
		],
	]);
	file_put_contents($finalConfigFile, $neon);

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
	$container = $containerFactory->create('/tmp', [sprintf('%s/config.level%s.neon', $containerFactory->getConfigDirectory(), $level), $finalConfigFile], [$codePath]);

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
