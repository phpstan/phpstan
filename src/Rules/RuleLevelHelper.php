<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PhpParser\Node\Expr;

class RuleLevelHelper
{

	public function isThis(Expr $expression): bool
	{
		if (!($expression instanceof Expr\Variable)) {
			return false;
		}

		if (!is_string($expression->name)) {
			return false;
		}

		return $expression->name === 'this';
	}

}
