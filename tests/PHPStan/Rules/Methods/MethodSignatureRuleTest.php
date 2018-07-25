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
					'Declaration of MethodSignature\SubClass::parameterTypeTest4(MethodSignature\Dog $animal): mixed should be compatible with MethodSignature\BaseClass::parameterTypeTest4(MethodSignature\Animal $animal): mixed',
					36,
				],
				[
					'Declaration of MethodSignature\SubClass::parameterTypeTest4(MethodSignature\Dog $animal): mixed should be compatible with MethodSignature\BaseInterface::parameterTypeTest4(MethodSignature\Animal $animal): mixed',
					36,
				],
				[
					'Declaration of MethodSignature\SubClass::parameterTypeTest5(MethodSignature\Dog $animal): mixed should be compatible with MethodSignature\BaseClass::parameterTypeTest5(MethodSignature\Cat $animal): mixed',
					43,
				],
				[
					'Declaration of MethodSignature\SubClass::parameterTypeTest5(MethodSignature\Dog $animal): mixed should be compatible with MethodSignature\BaseInterface::parameterTypeTest5(MethodSignature\Cat $animal): mixed',
					43,
				],
				[
					'Declaration of MethodSignature\SubClass::returnTypeTest1(): mixed should be compatible with MethodSignature\BaseClass::returnTypeTest1(): void',
					68,
				],
				[
					'Declaration of MethodSignature\SubClass::returnTypeTest1(): mixed should be compatible with MethodSignature\BaseInterface::returnTypeTest1(): void',
					68,
				],
				[
					'Declaration of MethodSignature\SubClass::returnTypeTest4(): MethodSignature\Animal should be compatible with MethodSignature\BaseClass::returnTypeTest4(): MethodSignature\Dog',
					89,
				],
				[
					'Declaration of MethodSignature\SubClass::returnTypeTest4(): MethodSignature\Animal should be compatible with MethodSignature\BaseInterface::returnTypeTest4(): MethodSignature\Dog',
					89,
				],
				[
					'Declaration of MethodSignature\SubClass::returnTypeTest5(): MethodSignature\Cat should be compatible with MethodSignature\BaseClass::returnTypeTest5(): MethodSignature\Dog',
					96,
				],
				[
					'Declaration of MethodSignature\SubClass::returnTypeTest5(): MethodSignature\Cat should be compatible with MethodSignature\BaseInterface::returnTypeTest5(): MethodSignature\Dog',
					96,
				],
				[
					'Declaration of MethodSignature\SubClassUsingTrait::parameterTypeTest4(MethodSignature\Dog $animal): mixed should be compatible with MethodSignature\BaseClass::parameterTypeTest4(MethodSignature\Animal $animal): mixed',
					262,
				],
				[
					'Declaration of MethodSignature\SubClassUsingTrait::parameterTypeTest4(MethodSignature\Dog $animal): mixed should be compatible with MethodSignature\BaseInterface::parameterTypeTest4(MethodSignature\Animal $animal): mixed',
					262,
				],
				[
					'Declaration of MethodSignature\SubClassUsingTrait::parameterTypeTest5(MethodSignature\Dog $animal): mixed should be compatible with MethodSignature\BaseClass::parameterTypeTest5(MethodSignature\Cat $animal): mixed',
					269,
				],
				[
					'Declaration of MethodSignature\SubClassUsingTrait::parameterTypeTest5(MethodSignature\Dog $animal): mixed should be compatible with MethodSignature\BaseInterface::parameterTypeTest5(MethodSignature\Cat $animal): mixed',
					269,
				],
				[
					'Declaration of MethodSignature\SubClassUsingTrait::returnTypeTest1(): mixed should be compatible with MethodSignature\BaseClass::returnTypeTest1(): void',
					294,
				],
				[
					'Declaration of MethodSignature\SubClassUsingTrait::returnTypeTest1(): mixed should be compatible with MethodSignature\BaseInterface::returnTypeTest1(): void',
					294,
				],
				[
					'Declaration of MethodSignature\SubClassUsingTrait::returnTypeTest4(): MethodSignature\Animal should be compatible with MethodSignature\BaseClass::returnTypeTest4(): MethodSignature\Dog',
					315,
				],
				[
					'Declaration of MethodSignature\SubClassUsingTrait::returnTypeTest4(): MethodSignature\Animal should be compatible with MethodSignature\BaseInterface::returnTypeTest4(): MethodSignature\Dog',
					315,
				],
				[
					'Declaration of MethodSignature\SubClassUsingTrait::returnTypeTest5(): MethodSignature\Cat should be compatible with MethodSignature\BaseClass::returnTypeTest5(): MethodSignature\Dog',
					322,
				],
				[
					'Declaration of MethodSignature\SubClassUsingTrait::returnTypeTest5(): MethodSignature\Cat should be compatible with MethodSignature\BaseInterface::returnTypeTest5(): MethodSignature\Dog',
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
					'Declaration of MethodSignature\SubClass::parameterTypeTest5(MethodSignature\Dog $animal): mixed should be compatible with MethodSignature\BaseClass::parameterTypeTest5(MethodSignature\Cat $animal): mixed',
					43,
				],
				[
					'Declaration of MethodSignature\SubClass::parameterTypeTest5(MethodSignature\Dog $animal): mixed should be compatible with MethodSignature\BaseInterface::parameterTypeTest5(MethodSignature\Cat $animal): mixed',
					43,
				],
				[
					'Declaration of MethodSignature\SubClass::returnTypeTest5(): MethodSignature\Cat should be compatible with MethodSignature\BaseClass::returnTypeTest5(): MethodSignature\Dog',
					96,
				],
				[
					'Declaration of MethodSignature\SubClass::returnTypeTest5(): MethodSignature\Cat should be compatible with MethodSignature\BaseInterface::returnTypeTest5(): MethodSignature\Dog',
					96,
				],
				[
					'Declaration of MethodSignature\SubClassUsingTrait::parameterTypeTest5(MethodSignature\Dog $animal): mixed should be compatible with MethodSignature\BaseClass::parameterTypeTest5(MethodSignature\Cat $animal): mixed',
					269,
				],
				[
					'Declaration of MethodSignature\SubClassUsingTrait::parameterTypeTest5(MethodSignature\Dog $animal): mixed should be compatible with MethodSignature\BaseInterface::parameterTypeTest5(MethodSignature\Cat $animal): mixed',
					269,
				],
				[
					'Declaration of MethodSignature\SubClassUsingTrait::returnTypeTest5(): MethodSignature\Cat should be compatible with MethodSignature\BaseClass::returnTypeTest5(): MethodSignature\Dog',
					322,
				],
				[
					'Declaration of MethodSignature\SubClassUsingTrait::returnTypeTest5(): MethodSignature\Cat should be compatible with MethodSignature\BaseInterface::returnTypeTest5(): MethodSignature\Dog',
					322,
				],
			]
		);
	}

}
