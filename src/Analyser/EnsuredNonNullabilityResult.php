<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

class EnsuredNonNullabilityResult
{

	/** @var MutatingScope */
	private $scope;

	/** @var EnsuredNonNullabilityResultExpression[] */
	private $specifiedExpressions;

	/**
	 * @param MutatingScope $scope
	 * @param EnsuredNonNullabilityResultExpression[] $specifiedExpressions
	 */
	public function __construct(MutatingScope $scope, array $specifiedExpressions)
	{
		$this->scope = $scope;
		$this->specifiedExpressions = $specifiedExpressions;
	}

	public function getScope(): MutatingScope
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
