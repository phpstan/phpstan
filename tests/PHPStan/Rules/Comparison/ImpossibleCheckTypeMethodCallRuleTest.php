<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Tests\AssertionClassMethodTypeSpecifyingExtension;
use PHPStan\Type\MethodTypeSpecifyingExtension;

class ImpossibleCheckTypeMethodCallRuleTest extends \PHPStan\Testing\RuleTestCase
{

	public function getRule(): \PHPStan\Rules\Rule
	{
		return new ImpossibleCheckTypeMethodCallRule(
			new ImpossibleCheckTypeHelper(
				$this->getTypeSpecifier()
			),
			true
		);
	}

	/**
	 * @return MethodTypeSpecifyingExtension[]
	 */
	protected function getMethodTypeSpecifyingExtensions(): array
	{
		return [
			new AssertionClassMethodTypeSpecifyingExtension(null),
			new class() implements MethodTypeSpecifyingExtension,
			TypeSpecifierAwareExtension {

				/** @var TypeSpecifier */
				private $typeSpecifier;

				public function setTypeSpecifier(TypeSpecifier $typeSpecifier): void
				{
					$this->typeSpecifier = $typeSpecifier;
				}

				public function getClass(): string
				{
					return \PHPStan\Tests\AssertionClass::class;
				}

				public function isMethodSupported(
					MethodReflection $methodReflection,
					MethodCall $node,
					TypeSpecifierContext $context
				): bool
				{
					return $methodReflection->getName() === 'assertNotInt'
						&& count($node->args) > 0;
				}

				public function specifyTypes(
					MethodReflection $methodReflection,
					MethodCall $node,
					Scope $scope,
					TypeSpecifierContext $context
				): SpecifiedTypes
				{
					return $this->typeSpecifier->specifyTypesInCondition(
						$scope,
						new \PhpParser\Node\Expr\BooleanNot(
							new \PhpParser\Node\Expr\FuncCall(
								new \PhpParser\Node\Name('is_int'),
								[
									$node->args[0],
								]
							)
						),
						TypeSpecifierContext::createTruthy()
					);
				}

			},
			new class() implements MethodTypeSpecifyingExtension,
			TypeSpecifierAwareExtension {

				/** @var TypeSpecifier */
				private $typeSpecifier;

				public function setTypeSpecifier(TypeSpecifier $typeSpecifier): void
				{
					$this->typeSpecifier = $typeSpecifier;
				}

				public function getClass(): string
				{
					return \ImpossibleMethodCall\Foo::class;
				}

				public function isMethodSupported(
					MethodReflection $methodReflection,
					MethodCall $node,
					TypeSpecifierContext $context
				): bool
				{
					return $methodReflection->getName() === 'isSame'
						&& count($node->args) >= 2;
				}

				public function specifyTypes(
					MethodReflection $methodReflection,
					MethodCall $node,
					Scope $scope,
					TypeSpecifierContext $context
				): SpecifiedTypes
				{
					return $this->typeSpecifier->specifyTypesInCondition(
						$scope,
						new \PhpParser\Node\Expr\BinaryOp\Identical(
							$node->args[0]->value,
							$node->args[1]->value
						),
						TypeSpecifierContext::createTruthy()
					);
				}

			},
			new class() implements MethodTypeSpecifyingExtension,
			TypeSpecifierAwareExtension {

				/** @var TypeSpecifier */
				private $typeSpecifier;

				public function setTypeSpecifier(TypeSpecifier $typeSpecifier): void
				{
					$this->typeSpecifier = $typeSpecifier;
				}

				public function getClass(): string
				{
					return \ImpossibleMethodCall\Foo::class;
				}

				public function isMethodSupported(
					MethodReflection $methodReflection,
					MethodCall $node,
					TypeSpecifierContext $context
				): bool
				{
					return $methodReflection->getName() === 'isNotSame'
						&& count($node->args) >= 2;
				}

				public function specifyTypes(
					MethodReflection $methodReflection,
					MethodCall $node,
					Scope $scope,
					TypeSpecifierContext $context
				): SpecifiedTypes
				{
					return $this->typeSpecifier->specifyTypesInCondition(
						$scope,
						new \PhpParser\Node\Expr\BinaryOp\NotIdentical(
							$node->args[0]->value,
							$node->args[1]->value
						),
						TypeSpecifierContext::createTruthy()
					);
				}

			},
		];
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/impossible-method-call.php'], [
			[
				'Call to method PHPStan\Tests\AssertionClass::assertString() with string will always evaluate to true.',
				14,
			],
			[
				'Call to method PHPStan\Tests\AssertionClass::assertString() with int will always evaluate to false.',
				15,
			],
			[
				'Call to method PHPStan\Tests\AssertionClass::assertNotInt() with int will always evaluate to false.',
				30,
			],
			[
				'Call to method PHPStan\Tests\AssertionClass::assertNotInt() with string will always evaluate to true.',
				36,
			],
			[
				'Call to method ImpossibleMethodCall\Foo::isSame() with 1 and 1 will always evaluate to true.',
				60,
			],
			[
				'Call to method ImpossibleMethodCall\Foo::isSame() with stdClass and stdClass will always evaluate to true.',
				78,
			],
		]);
	}

}
