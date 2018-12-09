<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

class MethodSignatureRuleTest extends \PHPStan\Testing\RuleTestCase
{

	/** @var bool */
	private $reportMaybes;

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new MethodSignatureRule($this->reportMaybes);
	}

	public function testReturnTypeRule(): void
	{
		$this->reportMaybes = true;
		$this->analyse(
			[
				__DIR__ . '/data/method-signature-definition.php',
				__DIR__ . '/data/method-signature.php',
			],
			[
				[
					'Parameter #1 $animal (MethodSignature\Dog) of method MethodSignature\SubClass::parameterTypeTest4() should be contravariant with parameter $animal (MethodSignature\Animal) of method MethodSignature\BaseClass::parameterTypeTest4()',
					36,
				],
				[
					'Parameter #1 $animal (MethodSignature\Dog) of method MethodSignature\SubClass::parameterTypeTest4() should be contravariant with parameter $animal (MethodSignature\Animal) of method MethodSignature\BaseInterface::parameterTypeTest4()',
					36,
				],
				[
					'Parameter #1 $animal (MethodSignature\Dog) of method MethodSignature\SubClass::parameterTypeTest5() should be compatible with parameter $animal (MethodSignature\Cat) of method MethodSignature\BaseClass::parameterTypeTest5()',
					43,
				],
				[
					'Parameter #1 $animal (MethodSignature\Dog) of method MethodSignature\SubClass::parameterTypeTest5() should be compatible with parameter $animal (MethodSignature\Cat) of method MethodSignature\BaseInterface::parameterTypeTest5()',
					43,
				],
				[
					'Return type (mixed) of method MethodSignature\SubClass::returnTypeTest1() should be covariant with return type (void) of method MethodSignature\BaseClass::returnTypeTest1()',
					68,
				],
				[
					'Return type (mixed) of method MethodSignature\SubClass::returnTypeTest1() should be covariant with return type (void) of method MethodSignature\BaseInterface::returnTypeTest1()',
					68,
				],
				[
					'Return type (MethodSignature\Animal) of method MethodSignature\SubClass::returnTypeTest4() should be covariant with return type (MethodSignature\Dog) of method MethodSignature\BaseClass::returnTypeTest4()',
					89,
				],
				[
					'Return type (MethodSignature\Animal) of method MethodSignature\SubClass::returnTypeTest4() should be covariant with return type (MethodSignature\Dog) of method MethodSignature\BaseInterface::returnTypeTest4()',
					89,
				],
				[
					'Return type (MethodSignature\Cat) of method MethodSignature\SubClass::returnTypeTest5() should be compatible with return type (MethodSignature\Dog) of method MethodSignature\BaseClass::returnTypeTest5()',
					96,
				],
				[
					'Return type (MethodSignature\Cat) of method MethodSignature\SubClass::returnTypeTest5() should be compatible with return type (MethodSignature\Dog) of method MethodSignature\BaseInterface::returnTypeTest5()',
					96,
				],
				[
					'Parameter #1 $animal (MethodSignature\Dog) of method MethodSignature\SubClassUsingTrait::parameterTypeTest4() should be contravariant with parameter $animal (MethodSignature\Animal) of method MethodSignature\BaseClass::parameterTypeTest4()',
					262,
				],
				[
					'Parameter #1 $animal (MethodSignature\Dog) of method MethodSignature\SubClassUsingTrait::parameterTypeTest4() should be contravariant with parameter $animal (MethodSignature\Animal) of method MethodSignature\BaseInterface::parameterTypeTest4()',
					262,
				],
				[
					'Parameter #1 $animal (MethodSignature\Dog) of method MethodSignature\SubClassUsingTrait::parameterTypeTest5() should be compatible with parameter $animal (MethodSignature\Cat) of method MethodSignature\BaseClass::parameterTypeTest5()',
					269,
				],
				[
					'Parameter #1 $animal (MethodSignature\Dog) of method MethodSignature\SubClassUsingTrait::parameterTypeTest5() should be compatible with parameter $animal (MethodSignature\Cat) of method MethodSignature\BaseInterface::parameterTypeTest5()',
					269,
				],
				[
					'Return type (mixed) of method MethodSignature\SubClassUsingTrait::returnTypeTest1() should be covariant with return type (void) of method MethodSignature\BaseClass::returnTypeTest1()',
					294,
				],
				[
					'Return type (mixed) of method MethodSignature\SubClassUsingTrait::returnTypeTest1() should be covariant with return type (void) of method MethodSignature\BaseInterface::returnTypeTest1()',
					294,
				],
				[
					'Return type (MethodSignature\Animal) of method MethodSignature\SubClassUsingTrait::returnTypeTest4() should be covariant with return type (MethodSignature\Dog) of method MethodSignature\BaseClass::returnTypeTest4()',
					315,
				],
				[
					'Return type (MethodSignature\Animal) of method MethodSignature\SubClassUsingTrait::returnTypeTest4() should be covariant with return type (MethodSignature\Dog) of method MethodSignature\BaseInterface::returnTypeTest4()',
					315,
				],
				[
					'Return type (MethodSignature\Cat) of method MethodSignature\SubClassUsingTrait::returnTypeTest5() should be compatible with return type (MethodSignature\Dog) of method MethodSignature\BaseClass::returnTypeTest5()',
					322,
				],
				[
					'Return type (MethodSignature\Cat) of method MethodSignature\SubClassUsingTrait::returnTypeTest5() should be compatible with return type (MethodSignature\Dog) of method MethodSignature\BaseInterface::returnTypeTest5()',
					322,
				],
			]
		);
	}

	public function testReturnTypeRuleWithoutMaybes(): void
	{
		$this->reportMaybes = false;
		$this->analyse(
			[
				__DIR__ . '/data/method-signature-definition.php',
				__DIR__ . '/data/method-signature.php',
			],
			[
				[
					'Parameter #1 $animal (MethodSignature\Dog) of method MethodSignature\SubClass::parameterTypeTest5() should be compatible with parameter $animal (MethodSignature\Cat) of method MethodSignature\BaseClass::parameterTypeTest5()',
					43,
				],
				[
					'Parameter #1 $animal (MethodSignature\Dog) of method MethodSignature\SubClass::parameterTypeTest5() should be compatible with parameter $animal (MethodSignature\Cat) of method MethodSignature\BaseInterface::parameterTypeTest5()',
					43,
				],
				[
					'Return type (MethodSignature\Cat) of method MethodSignature\SubClass::returnTypeTest5() should be compatible with return type (MethodSignature\Dog) of method MethodSignature\BaseClass::returnTypeTest5()',
					96,
				],
				[
					'Return type (MethodSignature\Cat) of method MethodSignature\SubClass::returnTypeTest5() should be compatible with return type (MethodSignature\Dog) of method MethodSignature\BaseInterface::returnTypeTest5()',
					96,
				],
				[
					'Parameter #1 $animal (MethodSignature\Dog) of method MethodSignature\SubClassUsingTrait::parameterTypeTest5() should be compatible with parameter $animal (MethodSignature\Cat) of method MethodSignature\BaseClass::parameterTypeTest5()',
					269,
				],
				[
					'Parameter #1 $animal (MethodSignature\Dog) of method MethodSignature\SubClassUsingTrait::parameterTypeTest5() should be compatible with parameter $animal (MethodSignature\Cat) of method MethodSignature\BaseInterface::parameterTypeTest5()',
					269,
				],
				[
					'Return type (MethodSignature\Cat) of method MethodSignature\SubClassUsingTrait::returnTypeTest5() should be compatible with return type (MethodSignature\Dog) of method MethodSignature\BaseClass::returnTypeTest5()',
					322,
				],
				[
					'Return type (MethodSignature\Cat) of method MethodSignature\SubClassUsingTrait::returnTypeTest5() should be compatible with return type (MethodSignature\Dog) of method MethodSignature\BaseInterface::returnTypeTest5()',
					322,
				],
			]
		);
	}

}
