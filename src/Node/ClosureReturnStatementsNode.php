<?php declare(strict_types = 1);

namespace PHPStan\Node;

use PhpParser\Node\Expr\Closure;
use PhpParser\NodeAbstract;
use PHPStan\Analyser\StatementResult;

class ClosureReturnStatementsNode extends NodeAbstract implements VirtualNode
{

	/** @var \PhpParser\Node\Expr\Closure */
	private $closureExpr;

	/** @var \PHPStan\Node\ReturnStatement[] */
	private $returnStatements;

	/** @var StatementResult */
	private $statementResult;

	/**
	 * @param \PhpParser\Node\Expr\Closure $closureExpr
	 * @param \PHPStan\Node\ReturnStatement[] $returnStatements
	 * @param \PHPStan\Analyser\StatementResult $statementResult
	 */
	public function __construct(
		Closure $closureExpr,
		array $returnStatements,
		StatementResult $statementResult
	)
	{
		parent::__construct($closureExpr->getAttributes());
		$this->closureExpr = $closureExpr;
		$this->returnStatements = $returnStatements;
		$this->statementResult = $statementResult;
	}

	public function getClosureExpr(): Closure
	{
		return $this->closureExpr;
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
		return 'PHPStan_Node_ClosureReturnStatementsNode';
	}

	public function getSubNodeNames(): array
	{
		return [];
	}

}
