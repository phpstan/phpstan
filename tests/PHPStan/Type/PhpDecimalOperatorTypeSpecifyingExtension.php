<?php declare(strict_types = 1);

namespace PHPStan\Type;

final class PhpDecimalOperatorTypeSpecifyingExtension implements OperatorTypeSpecifyingExtension
{

	public function isOperatorSupported(string $operatorSigil, Type $leftSide, Type $rightSide): bool
	{
		return in_array($operatorSigil, ['-', '+', '*', '/'], true)
			&& $leftSide->isSuperTypeOf(new ObjectType(\Decimal\Decimal::class))->yes()
			&& $rightSide->isSuperTypeOf(new ObjectType(\Decimal\Decimal::class))->yes();
	}

	public function specifyType(string $operatorSigil, Type $leftSide, Type $rightSide): Type
	{
		return new ObjectType(\Decimal\Decimal::class);
	}

}
