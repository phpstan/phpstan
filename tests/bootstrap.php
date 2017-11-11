<?php declare(strict_types = 1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/PHPStan/Rules/AbstractRuleTest.php';
require_once __DIR__ . '/PHPStan/Rules/AlwaysFailRule.php';
require_once __DIR__ . '/PHPStan/Rules/DummyRule.php';

PHPStan\Type\TypeCombinator::setUnionTypesEnabled(true);
require_once __DIR__ . '/phpstan-bootstrap.php';
