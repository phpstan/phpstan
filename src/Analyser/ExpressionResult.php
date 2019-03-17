<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

class ExpressionResult
{

	/** @var Scope */
	private $scope;

	/** @var bool */
	private $hasYield;

	/** @var (callable(): Scope)|null */
	private $truthyScopeCallback;

	/** @var Scope|null */
	private $truthyScope;

	/** @var (callable(): Scope)|null */
	private $falseyScopeCallback;

	/** @var Scope|null */
	private $falseyScope;

	/**
	 * @param Scope $scope
	 * @param bool $hasYield
	 * @param (callable(): Scope)|null $truthyScopeCallback
	 * @param (callable(): Scope)|null $falseyScopeCallback
	 */
	public function __construct(
		Scope $scope,
		bool $hasYield,
		?callable $truthyScopeCallback = null,
		?callable $falseyScopeCallback = null
	)
	{
		$this->scope = $scope;
		$this->hasYield = $hasYield;
		$this->truthyScopeCallback = $truthyScopeCallback;
		$this->falseyScopeCallback = $falseyScopeCallback;
	}

	public function getScope(): Scope
	{
		return $this->scope;
	}

	public function hasYield(): bool
	{
		return $this->hasYield;
	}

	public function getTruthyScope(): Scope
	{
		if ($this->truthyScopeCallback === null) {
			return $this->scope;
		}

		if ($this->truthyScope !== null) {
			return $this->truthyScope;
		}

		$callback = $this->truthyScopeCallback;
		$this->truthyScope = $callback();
		return $this->truthyScope;
	}

	public function getFalseyScope(): Scope
	{
		if ($this->falseyScopeCallback === null) {
			return $this->scope;
		}

		if ($this->falseyScope !== null) {
			return $this->falseyScope;
		}

		$callback = $this->falseyScopeCallback;
		$this->falseyScope = $callback();
		return $this->falseyScope;
	}

}
