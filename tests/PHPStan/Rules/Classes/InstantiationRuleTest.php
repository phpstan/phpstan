<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PHPStan\Rules\FunctionCallParametersCheck;

class InstantiationRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		$broker = $this->createBroker();
		return new InstantiationRule(
			$broker,
			new FunctionCallParametersCheck($broker, true)
		);
	}

	public function testInstantiation()
	{
		$this->analyse(
			[__DIR__ . '/data/instantiation.php'],
			[
				[
					'Class TestInstantiation\InstantiatingClass constructor invoked with 0 parameters, 1 required.',
					15,
				],
				[
					'Class TestInstantiation\FooInstantiation does not have a constructor and must be instantiated without any parameters.',
					25,
				],
				[
					'Instantiated class TestInstantiation\FooBarInstantiation not found.',
					26,
				],
				[
					'Class TestInstantiation\BarInstantiation constructor invoked with 0 parameters, 1 required.',
					27,
				],
				[
					'Instantiated class TestInstantiation\LoremInstantiation is abstract.',
					28,
				],
				[
					'Cannot instantiate interface TestInstantiation\IpsumInstantiation.',
					29,
				],
				[
					'Class DatePeriod constructor invoked with 0 parameters, 1-4 required.',
					35,
				],
				[
					'Using self outside of class scope.',
					38,
				],
				[
					'Using static outside of class scope.',
					39,
				],
			]
		);
	}

}
