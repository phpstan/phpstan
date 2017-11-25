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

	public function __construct(
		Scope $scope,
		array $statements,
		bool $filterByTruthyValue = false
	)
	{
		$this->scope = $scope;
		$this->statements = $statements;
		$this->filterByTruthyValue = $filterByTruthyValue;
	}

	public function getScope(): Scope
	{
		return $this->scope;
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
