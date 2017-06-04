<?php declare(strict_types = 1);

use Nette\Configurator;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/TestCase.php';
require_once __DIR__ . '/PHPStan/Rules/AbstractRuleTest.php';
require_once __DIR__ . '/PHPStan/Rules/AlwaysFailRule.php';
require_once __DIR__ . '/PHPStan/Rules/DummyRule.php';

$rootDir = __DIR__ . '/..';
$tmpDir = $rootDir . '/tmp';
$confDir = $rootDir . '/conf';

$configurator = new Configurator();
$configurator->defaultExtensions = [];
$configurator->setDebugMode(true);
$configurator->setTempDirectory($tmpDir);
$configurator->addConfig($confDir . '/config.neon');
$configurator->addConfig($confDir . '/config.level7.neon');
$configurator->addParameters([
	'rootDir' => $rootDir,
	'tmpDir' => $tmpDir,
	'currentWorkingDirectory' => $rootDir,
	'cliArgumentsVariablesRegistered' => false,
]);
$container = $configurator->createContainer();

PHPStan\TestCase::setContainer($container);
PHPStan\Type\TypeCombinator::setUnionTypesEnabled(true);
require_once __DIR__ . '/phpstan-bootstrap.php';
