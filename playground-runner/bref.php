<?php declare(strict_types = 1);

use PHPStan\AnalysedCodeException;
use PHPStan\Analyser\Error;
use PHPStan\Analyser\RuleErrorTransformer;
use PHPStan\Analyser\ScopeContext;
use PHPStan\Analyser\ScopeFactory;
use PHPStan\BetterReflection\NodeCompiler\Exception\UnableToCompileNode;
use PHPStan\BetterReflection\Reflection\Exception\CircularReference;
use PHPStan\BetterReflection\Reflector\Exception\IdentifierNotFound;
use PHPStan\Collectors\CollectedData;
use PHPStan\Node\CollectedDataNode;
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

/**
 * @param CollectedData[] $collectedData
 * @return Error[]
 */
function getCollectedDataErrors(\PHPStan\DependencyInjection\Container $container, array $collectedData): array
{
	$nodeType = CollectedDataNode::class;
	$node = new CollectedDataNode($collectedData);
	$file = 'N/A';
	$scope = $container->getByType(ScopeFactory::class)->create(ScopeContext::create($file));
	$ruleRegistry = $container->getByType(\PHPStan\Rules\Registry::class);
	$ruleErrorTransformer = $container->getByType(RuleErrorTransformer::class);
	$errors = [];
	foreach ($ruleRegistry->getRules($nodeType) as $rule) {
		try {
			$ruleErrors = $rule->processNode($node, $scope);
		} catch (AnalysedCodeException $e) {
			$errors[] = new Error($e->getMessage(), $file, $node->getLine(), $e, null, null, $e->getTip());
			continue;
		} catch (IdentifierNotFound $e) {
			$errors[] = new Error(sprintf('Reflection error: %s not found.', $e->getIdentifier()->getName()), $file, $node->getLine(), $e, null, null, 'Learn more at https://phpstan.org/user-guide/discovering-symbols');
			continue;
		} catch (UnableToCompileNode | CircularReference $e) {
			$errors[] = new Error(sprintf('Reflection error: %s', $e->getMessage()), $file, $node->getLine(), $e);
			continue;
		}

		foreach ($ruleErrors as $ruleError) {
			$errors[] = $ruleErrorTransformer->transform($ruleError, $scope, $nodeType, $node->getLine());
		}
	}

	return $errors;
}

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
	$hasInternalErrors = count($analyserResult->getInternalErrors()) > 0 || $analyserResult->hasReachedInternalErrorsCountLimit();
	$results = $analyserResult->getErrors();

	if (!$hasInternalErrors) {
		foreach (getCollectedDataErrors($container, $analyserResult->getCollectedData()) as $error) {
			$results[] = $error;
		}
	}

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
