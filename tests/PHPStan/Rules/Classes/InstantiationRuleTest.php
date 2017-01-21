<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PHPStan\Rules\FunctionCallParametersCheck;

class InstantiationRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new InstantiationRule(
			$this->createBroker(),
			new FunctionCallParametersCheck(true)
		);
	}

	public function testInstantiation()
	{
		require_once __DIR__ . '/data/instantiation-classes.php';
		$this->analyse(
			[__DIR__ . '/data/instantiation.php'],
			[
				[
					'Class TestInstantiation\FooInstantiation does not have a constructor and must be instantiated without any parameters.',
					7,
				],
				[
					'Instantiated class TestInstantiation\FooBarInstantiation not found.',
					8,
				],
				[
					'Class TestInstantiation\BarInstantiation constructor invoked with 0 parameters, 1 required.',
					9,
				],
				[
					'Instantiated class TestInstantiation\LoremInstantiation is abstract.',
					10,
				],
				[
					'Cannot instantiate interface TestInstantiation\IpsumInstantiation.',
					11,
				],
				[
					'Class DatePeriod constructor invoked with 0 parameters, 3-4 required.',
					17,
				],
			]
		);
	}

}
