<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

class StatementList
{

	/** @var \PHPStan\Analyser\Scope */
	private $scope;

	/** @var \PhpParser\Node[] */
	private $statements;

	/** @var bool */
	private $carryOverSpecificTypes;

	public function __construct(
		Scope $scope,
		array $statements,
		bool $carryOverSpecificTypes
	)
	{
		$this->scope = $scope;
		$this->statements = $statements;
		$this->carryOverSpecificTypes = $carryOverSpecificTypes;
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

	public function shouldCarryOverSpecificTypes(): bool
	{
		return $this->carryOverSpecificTypes;
	}

}
