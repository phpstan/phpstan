<?php declare(strict_types = 1);

namespace PHPStan\Node;

use PhpParser\Node;
use PhpParser\NodeAbstract;
use PHPStan\Analyser\StatementResult;

class ExecutionEndNode extends NodeAbstract implements VirtualNode
{

	/** @var Node */
	private $node;

	/** @var StatementResult */
	private $statementResult;

	public function __construct(Node $node, StatementResult $statementResult)
	{
		parent::__construct($node->getAttributes());
		$this->node = $node;
		$this->statementResult = $statementResult;
	}

	public function getNode(): Node
	{
		return $this->node;
	}

	public function getStatementResult(): StatementResult
	{
		return $this->statementResult;
	}

	public function getType(): string
	{
		return 'PHPStan_Node_ExecutionEndNode';
	}

	public function getSubNodeNames(): array
	{
		return [];
	}

}
