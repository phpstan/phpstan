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

	$finalConfigFile = '/tmp/run-phpstan-tmp.neon';
	$neon = \Nette\Neon\Neon::encode([
		'includes' => $configFiles,
		'parameters' => [
			'inferPrivatePropertyTypeFromConstructor' => true,
			'treatPhpDocTypesAsCertain' => $event['treatPhpDocTypesAsCertain'] ?? true,
			'phpVersion' => $event['phpVersion'] ?? 80000,
			'featureToggles' => [
				'disableRuntimeReflectionProvider' => true,
			],
            'sourceLocatorPlaygroundMode' => true,
		],
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
	require_once 'phar://' . $rootDir . '/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/Attribute.php';
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
		$errors[] = $error;
	}

	return ['result' => $errors, 'version' => $phpstanVersion];
};
