<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Fixture\TestDecimal;

final class TestDecimalOperatorTypeSpecifyingExtension implements OperatorTypeSpecifyingExtension
{

	public function isOperatorSupported(string $operatorSigil, Type $leftSide, Type $rightSide): bool
	{
		return in_array($operatorSigil, ['-', '+', '*', '/'], true)
			&& $leftSide->isSuperTypeOf(new ObjectType(TestDecimal::class))->yes()
			&& $rightSide->isSuperTypeOf(new ObjectType(TestDecimal::class))->yes();
	}

	public function specifyType(string $operatorSigil, Type $leftSide, Type $rightSide): Type
	{
		return new ObjectType(TestDecimal::class);
	}

}
