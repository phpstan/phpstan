<?php declare(strict_types = 1);

use PHPStan\DependencyInjection\ContainerFactory;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/TestCase.php';
require_once __DIR__ . '/PHPStan/Rules/AbstractRuleTest.php';
require_once __DIR__ . '/PHPStan/Rules/AlwaysFailRule.php';
require_once __DIR__ . '/PHPStan/Rules/DummyRule.php';

$rootDir = __DIR__ . '/..';
$containerFactory = new ContainerFactory($rootDir);
$container = $containerFactory->create($rootDir . '/tmp', [
	$containerFactory->getConfigDirectory() . '/config.level7.neon',
]);

PHPStan\TestCase::setContainer($container);
PHPStan\Type\TypeCombinator::setUnionTypesEnabled(true);
require_once __DIR__ . '/phpstan-bootstrap.php';
