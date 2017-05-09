<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

class StatementList
{

	/** @var \PHPStan\Analyser\Scope */
	private $scope;

	/** @var \PhpParser\Node[] */
	private $statements;

	public function __construct(
		Scope $scope,
		array $statements
	)
	{
		$this->scope = $scope;
		$this->statements = $statements;
	}

	public function getScope(): Scope
	{
		return $this->scope;
	}

	/**
	 * @return \PhpParser\Node[]|null
	 */
	public function getStatements()
	{
		return $this->statements;
	}

}
