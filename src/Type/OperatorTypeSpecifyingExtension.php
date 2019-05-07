<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Analyser\SpecifiedTypes;

interface OperatorTypeSpecifyingExtension
{

	public function isOperatorSupported(string $operatorSigil, Type $leftSide, Type $rightSide): bool;

	public function specifyType(string $operatorSigil, Type $leftSide, Type $rightSide): Type;

}
