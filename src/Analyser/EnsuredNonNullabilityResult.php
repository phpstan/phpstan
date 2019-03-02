<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

class EnsuredNonNullabilityResult
{

	/** @var Scope */
	private $scope;

	/** @var EnsuredNonNullabilityResultExpression[] */
	private $specifiedExpressions;

	/**
	 * @param Scope $scope
	 * @param EnsuredNonNullabilityResultExpression[] $specifiedExpressions
	 */
	public function __construct(Scope $scope, array $specifiedExpressions)
	{
		$this->scope = $scope;
		$this->specifiedExpressions = $specifiedExpressions;
	}

	public function getScope(): Scope
	{
		return $this->scope;
	}

	/**
	 * @return EnsuredNonNullabilityResultExpression[]
	 */
	public function getSpecifiedExpressions(): array
	{
		return $this->specifiedExpressions;
	}

}
