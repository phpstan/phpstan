<?php declare(strict_types = 1);

use PHPStan\Testing\TestCase;
use PHPStan\Type\TypeCombinator;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/PHPStan/Rules/AlwaysFailRule.php';
require_once __DIR__ . '/PHPStan/Rules/DummyRule.php';
require_once __DIR__ . '/phpstan-bootstrap.php';

eval('trait TraitInEval {

	/**
	 * @param int $i
	 */
	public function doFoo($i)
	{
	}

}');

TestCase::getContainer();
TypeCombinator::$enableSubtractableTypes = true;
