<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

class MethodSignatureRuleTest extends \PHPStan\Testing\RuleTestCase
{

	/** @var bool */
	private $reportMaybes;

	/** @var bool */
	private $reportStatic;

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new MethodSignatureRule($this->reportMaybes, $this->reportStatic);
	}

	public function testReturnTypeRule(): void
	{
		$this->reportMaybes = true;
		$this->reportStatic = true;
		$this->analyse(
			[
				__DIR__ . '/data/method-signature.php',
			],
			[
				[
					'Parameter #1 $animal (MethodSignature\Dog) of method MethodSignature\SubClass::parameterTypeTest4() should be contravariant with parameter $animal (MethodSignature\Animal) of method MethodSignature\BaseClass::parameterTypeTest4()',
					298,
				],
				[
					'Parameter #1 $animal (MethodSignature\Dog) of method MethodSignature\SubClass::parameterTypeTest4() should be contravariant with parameter $animal (MethodSignature\Animal) of method MethodSignature\BaseInterface::parameterTypeTest4()',
					298,
				],
				[
					'Parameter #1 $animal (MethodSignature\Dog) of method MethodSignature\SubClass::parameterTypeTest5() should be compatible with parameter $animal (MethodSignature\Cat) of method MethodSignature\BaseClass::parameterTypeTest5()',
					305,
				],
				[
					'Parameter #1 $animal (MethodSignature\Dog) of method MethodSignature\SubClass::parameterTypeTest5() should be compatible with parameter $animal (MethodSignature\Cat) of method MethodSignature\BaseInterface::parameterTypeTest5()',
					305,
				],
				[
					'Return type (MethodSignature\Animal) of method MethodSignature\SubClass::returnTypeTest4() should be covariant with return type (MethodSignature\Dog) of method MethodSignature\BaseClass::returnTypeTest4()',
					351,
				],
				[
					'Return type (MethodSignature\Animal) of method MethodSignature\SubClass::returnTypeTest4() should be covariant with return type (MethodSignature\Dog) of method MethodSignature\BaseInterface::returnTypeTest4()',
					351,
				],
				[
					'Return type (MethodSignature\Cat) of method MethodSignature\SubClass::returnTypeTest5() should be compatible with return type (MethodSignature\Dog) of method MethodSignature\BaseClass::returnTypeTest5()',
					358,
				],
				[
					'Return type (MethodSignature\Cat) of method MethodSignature\SubClass::returnTypeTest5() should be compatible with return type (MethodSignature\Dog) of method MethodSignature\BaseInterface::returnTypeTest5()',
					358,
				],
			]
		);
	}

	public function testReturnTypeRuleTrait(): void
	{
		$this->reportMaybes = true;
		$this->reportStatic = true;
		$this->analyse(
			[
				__DIR__ . '/data/method-signature-trait.php',
			],
			[
				[
					'Parameter #1 $animal (MethodSignature\Dog) of method MethodSignature\SubClassUsingTrait::parameterTypeTest4() should be contravariant with parameter $animal (MethodSignature\Animal) of method MethodSignature\BaseClass::parameterTypeTest4()',
					43,
				],
				[
					'Parameter #1 $animal (MethodSignature\Dog) of method MethodSignature\SubClassUsingTrait::parameterTypeTest4() should be contravariant with parameter $animal (MethodSignature\Animal) of method MethodSignature\BaseInterface::parameterTypeTest4()',
					43,
				],
				[
					'Parameter #1 $animal (MethodSignature\Dog) of method MethodSignature\SubClassUsingTrait::parameterTypeTest5() should be compatible with parameter $animal (MethodSignature\Cat) of method MethodSignature\BaseClass::parameterTypeTest5()',
					50,
				],
				[
					'Parameter #1 $animal (MethodSignature\Dog) of method MethodSignature\SubClassUsingTrait::parameterTypeTest5() should be compatible with parameter $animal (MethodSignature\Cat) of method MethodSignature\BaseInterface::parameterTypeTest5()',
					50,
				],
				[
					'Return type (MethodSignature\Animal) of method MethodSignature\SubClassUsingTrait::returnTypeTest4() should be covariant with return type (MethodSignature\Dog) of method MethodSignature\BaseClass::returnTypeTest4()',
					96,
				],
				[
					'Return type (MethodSignature\Animal) of method MethodSignature\SubClassUsingTrait::returnTypeTest4() should be covariant with return type (MethodSignature\Dog) of method MethodSignature\BaseInterface::returnTypeTest4()',
					96,
				],
				[
					'Return type (MethodSignature\Cat) of method MethodSignature\SubClassUsingTrait::returnTypeTest5() should be compatible with return type (MethodSignature\Dog) of method MethodSignature\BaseClass::returnTypeTest5()',
					103,
				],
				[
					'Return type (MethodSignature\Cat) of method MethodSignature\SubClassUsingTrait::returnTypeTest5() should be compatible with return type (MethodSignature\Dog) of method MethodSignature\BaseInterface::returnTypeTest5()',
					103,
				],
			]
		);
	}

	public function testReturnTypeRuleTraitWithoutMaybes(): void
	{
		$this->reportMaybes = false;
		$this->reportStatic = true;
		$this->analyse(
			[
				__DIR__ . '/data/method-signature-trait.php',
			],
			[
				[
					'Parameter #1 $animal (MethodSignature\Dog) of method MethodSignature\SubClassUsingTrait::parameterTypeTest5() should be compatible with parameter $animal (MethodSignature\Cat) of method MethodSignature\BaseClass::parameterTypeTest5()',
					50,
				],
				[
					'Parameter #1 $animal (MethodSignature\Dog) of method MethodSignature\SubClassUsingTrait::parameterTypeTest5() should be compatible with parameter $animal (MethodSignature\Cat) of method MethodSignature\BaseInterface::parameterTypeTest5()',
					50,
				],
				[
					'Return type (MethodSignature\Cat) of method MethodSignature\SubClassUsingTrait::returnTypeTest5() should be compatible with return type (MethodSignature\Dog) of method MethodSignature\BaseClass::returnTypeTest5()',
					103,
				],
				[
					'Return type (MethodSignature\Cat) of method MethodSignature\SubClassUsingTrait::returnTypeTest5() should be compatible with return type (MethodSignature\Dog) of method MethodSignature\BaseInterface::returnTypeTest5()',
					103,
				],
			]
		);
	}

	public function testReturnTypeRuleWithoutMaybes(): void
	{
		$this->reportMaybes = false;
		$this->reportStatic = true;
		$this->analyse(
			[
				__DIR__ . '/data/method-signature.php',
			],
			[
				[
					'Parameter #1 $animal (MethodSignature\Dog) of method MethodSignature\SubClass::parameterTypeTest5() should be compatible with parameter $animal (MethodSignature\Cat) of method MethodSignature\BaseClass::parameterTypeTest5()',
					305,
				],
				[
					'Parameter #1 $animal (MethodSignature\Dog) of method MethodSignature\SubClass::parameterTypeTest5() should be compatible with parameter $animal (MethodSignature\Cat) of method MethodSignature\BaseInterface::parameterTypeTest5()',
					305,
				],
				[
					'Return type (MethodSignature\Cat) of method MethodSignature\SubClass::returnTypeTest5() should be compatible with return type (MethodSignature\Dog) of method MethodSignature\BaseClass::returnTypeTest5()',
					358,
				],
				[
					'Return type (MethodSignature\Cat) of method MethodSignature\SubClass::returnTypeTest5() should be compatible with return type (MethodSignature\Dog) of method MethodSignature\BaseInterface::returnTypeTest5()',
					358,
				],
			]
		);
	}

	public function testRuleWithoutStaticMethods(): void
	{
		$this->reportMaybes = true;
		$this->reportStatic = false;
		$this->analyse([__DIR__ . '/data/method-signature-static.php'], []);
	}

	public function testRuleWithStaticMethods(): void
	{
		$this->reportMaybes = true;
		$this->reportStatic = true;
		$this->analyse([__DIR__ . '/data/method-signature-static.php'], [
			[
				'Parameter #1 $value (string) of method MethodSignature\Bar::doFoo() should be compatible with parameter $value (int) of method MethodSignature\Foo::doFoo()',
				24,
			],
		]);
	}

}
