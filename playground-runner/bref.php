<?php declare(strict_types = 1);

require __DIR__.'/vendor/autoload.php';

error_reporting(E_ALL);
ini_set('display_errors', '1');

$client = new Raven_Client('https://35e1e4a8936c4b70b8377056a5eeaeeb@sentry.io/1319523');
$errorHandler = new Raven_ErrorHandler($client);
$errorHandler->registerExceptionHandler();
$errorHandler->registerErrorHandler();
$errorHandler->registerShutdownFunction();

lambda(function (array $event) {
	$code = $event['code'];
	$level = $event['level'];
	$codePath = '/tmp/tmp.php';
	file_put_contents($codePath, $code);

	$errorHandler = new \PhpParser\ErrorHandler\Collecting();
	$sanitizer = new \App\CodeSanitizer(
		new PhpParser\Parser\Php7(new \PhpParser\Lexer()),
		new \PhpParser\PrettyPrinter\Standard(),
		$errorHandler
	);
	$autoloadPath = '/tmp/autoload.php';
	$sanitizedCode = $sanitizer->sanitize($code);
	if ($errorHandler->hasErrors()) {
		$errors = [];
		foreach ($errorHandler->getErrors() as $error) {
			$errors[] = [
				'message' => $error->getRawMessage(),
				'line' => $error->getStartLine(),
			];
		}
		return [
			'result' => $errors,
			'version' => \Jean85\PrettyVersions::getVersion('phpstan/phpstan')->getPrettyVersion(),
		];
	}

	$rootDir = getenv('LAMBDA_TASK_ROOT');
	file_put_contents($autoloadPath, $sanitizedCode);
	exec('php ' . escapeshellarg($autoloadPath), $outputArray, $exitCode);
	if ($exitCode !== 0) {
		$message = trim(implode("\n", $outputArray));
		$line = 1;
		if (preg_match('#on line (\d+)#', $message, $matches)) {
			$line = (int) $matches[1];
			$message = trim(str_replace('on line ' . $line, '', $message));
		}
		$message = trim(str_replace('in /tmp/autoload.php', '', $message));

		return [
			'result' => [
				[
					'message' => $message,
					'line' => $line,
				],
			],
			'version' => \Jean85\PrettyVersions::getVersion('phpstan/phpstan')->getPrettyVersion(),
		];
	}

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
		],
	]);
	file_put_contents($finalConfigFile, $neon);

	require $autoloadPath;

	$containerFactory = new \PHPStan\DependencyInjection\ContainerFactory('/tmp');
	$container = $containerFactory->create('/tmp', [sprintf('%s/config.level%s.neon', $containerFactory->getConfigDirectory(), $level), $finalConfigFile], [$codePath]);

	/** @var \PHPStan\Analyser\Analyser $analyser */
	$analyser = $container->getByType(\PHPStan\Analyser\Analyser::class);

	/** @var \PHPStan\Analyser\NodeScopeResolver $nodeScopeResolver */
	$nodeScopeResolver = $container->getByType(\PHPStan\Analyser\NodeScopeResolver::class);
	$results = $analyser->analyse([$codePath], true, function (string $file) use ($nodeScopeResolver, $codePath, $autoloadPath): void {
		$nodeScopeResolver->setAnalysedFiles([$codePath, $autoloadPath]);
	}, null, false);

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

	return ['result' => $errors, 'version' => \Jean85\PrettyVersions::getVersion('phpstan/phpstan')->getPrettyVersion()];
});
