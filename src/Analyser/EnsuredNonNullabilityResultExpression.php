<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PhpParser\Node\Expr;
use PHPStan\Type\Type;

class EnsuredNonNullabilityResultExpression
{

	/** @var Expr */
	private $expression;

	/** @var Type */
	private $originalType;

	public function __construct(Expr $expression, Type $originalType)
	{
		$this->expression = $expression;
		$this->originalType = $originalType;
	}

	public function getExpression(): Expr
	{
		return $this->expression;
	}

	public function getOriginalType(): Type
	{
		return $this->originalType;
	}

}
