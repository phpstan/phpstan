<?php declare(strict_types = 1);

namespace PHPStan\Node;

use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeAbstract;
use PHPStan\Analyser\StatementResult;

class FunctionReturnStatementsNode extends NodeAbstract implements VirtualNode
{

	/** @var \PHPStan\Node\ReturnStatement[] */
	private $returnStatements;

	/** @var StatementResult */
	private $statementResult;

	/**
	 * @param \PhpParser\Node\Stmt\Function_ $function
	 * @param \PHPStan\Node\ReturnStatement[] $returnStatements
	 * @param \PHPStan\Analyser\StatementResult $statementResult
	 */
	public function __construct(
		Function_ $function,
		array $returnStatements,
		StatementResult $statementResult
	)
	{
		parent::__construct($function->getAttributes());
		$this->returnStatements = $returnStatements;
		$this->statementResult = $statementResult;
	}

	/**
	 * @return \PHPStan\Node\ReturnStatement[]
	 */
	public function getReturnStatements(): array
	{
		return $this->returnStatements;
	}

	public function getStatementResult(): StatementResult
	{
		return $this->statementResult;
	}

	public function getType(): string
	{
		return 'PHPStan_Node_FunctionReturnStatementsNode';
	}

	public function getSubNodeNames(): array
	{
		return [];
	}

}
