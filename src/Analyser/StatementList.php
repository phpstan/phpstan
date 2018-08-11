<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

class StatementList
{

	/** @var \PHPStan\Analyser\Scope */
	private $scope;

	/** @var \PhpParser\Node[] */
	private $statements;

	/** @var bool */
	private $filterByTruthyValue;

	/** @var callable(Scope $scope): Scope|null */
	private $processScope;

	/**
	 * @param Scope $scope
	 * @param \PhpParser\Node[] $statements
	 * @param bool $filterByTruthyValue
	 * @param callable(Scope $scope): Scope|null $processScope
	 */
	public function __construct(
		Scope $scope,
		array $statements,
		bool $filterByTruthyValue = false,
		?callable $processScope = null
	)
	{
		$this->scope = $scope;
		$this->statements = $statements;
		$this->filterByTruthyValue = $filterByTruthyValue;
		$this->processScope = $processScope;
	}

	public static function fromList(Scope $scope, self $list): self
	{
		return new self(
			$scope,
			$list->statements,
			$list->filterByTruthyValue,
			$list->processScope
		);
	}

	public function getScope(): Scope
	{
		$scope = $this->scope;
		if ($this->processScope !== null) {
			$callback = $this->processScope;
			$scope = $callback($scope);
		}

		return $scope;
	}

	/**
	 * @return \PhpParser\Node[]
	 */
	public function getStatements(): array
	{
		return $this->statements;
	}

	public function shouldFilterByTruthyValue(): bool
	{
		return $this->filterByTruthyValue;
	}

}
