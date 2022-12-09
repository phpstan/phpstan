<?php declare(strict_types = 1);

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
	$configFiles = [];
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
            'exceptions' => [
                'check' => [
                    'missingCheckedExceptionInThrows' => $event['exceptions'] ?? false,
                    'tooWideThrowType' => $event['exceptions'] ?? false,
                    'implicitThrows' => $event['exceptions'] ?? true,
                ]
            ]
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
	$results = $analyser->analyse([$codePath], null, null, false, [$codePath])->getErrors();

	error_clear_last();

	$errors = [];
	foreach ($results as $result) {
		if (is_string($result)) {
			$errors[] = [
				'message' => $result,
				'line' => 1,
			];
			continue;
		}

		$errors[] = [
			'message' => $result->getMessage(),
			'line' => $result->getLine(),
		];
	}

	return ['result' => $errors, 'version' => $phpstanVersion];
};
