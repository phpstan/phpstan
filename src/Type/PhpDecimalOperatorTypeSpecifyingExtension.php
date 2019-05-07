<?php

declare(strict_types = 1);

namespace PHPStan\Type;

final class PhpDecimalOperatorTypeSpecifyingExtension implements OperatorTypeSpecifyingExtension
{

	public function isOperatorSupported(string $operatorSigil, Type $leftSide, Type $rightSide): bool
	{
		return in_array($operatorSigil, ['-', '+', '*', '/'])
			&& $leftSide->equals(new ObjectType(\Decimal\Decimal::class))
			&& $rightSide->equals(new ObjectType(\Decimal\Decimal::class));
	}

	public function specifyType(string $operatorSigil, Type $leftSide, Type $rightSide): Type
	{
		return new ObjectType(\Decimal\Decimal::class);
	}
}
