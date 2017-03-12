<?php declare(strict_types = 1);

use \PHPStan\Rules\RegistryFactory;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/TestCase.php';
require_once __DIR__ . '/PHPStan/Rules/AbstractRuleTest.php';
require_once __DIR__ . '/PHPStan/Rules/AlwaysFailRule.php';
require_once __DIR__ . '/PHPStan/Rules/DummyRule.php';

$rootDir = __DIR__ . '/..';
$confDir = $rootDir . '/conf';

$builder = new \DI\ContainerBuilder();
$builder->addDefinitions($confDir.'/config.php');

$container = $builder->build();
$container->set(\Interop\Container\ContainerInterface::class, $container);

// for level 5
$container->set('checkThisOnly', false);
$container->set('checkFunctionArgumentTypes', true);
$container->set('enableUnionTypes', true);
RegistryFactory::setRules(RegistryFactory::getRuleArgList(5));

PHPStan\TestCase::setContainer($container);
require_once __DIR__ . '/phpstan-bootstrap.php';
