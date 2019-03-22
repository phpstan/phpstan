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

	/** @var bool */
	private $hasNativeReturnTypehint;

	public function __construct(
		Node $node,
		StatementResult $statementResult,
		bool $hasNativeReturnTypehint
	)
	{
		parent::__construct($node->getAttributes());
		$this->node = $node;
		$this->statementResult = $statementResult;
		$this->hasNativeReturnTypehint = $hasNativeReturnTypehint;
	}

	public function getNode(): Node
	{
		return $this->node;
	}

	public function getStatementResult(): StatementResult
	{
		return $this->statementResult;
	}

	public function hasNativeReturnTypehint(): bool
	{
		return $this->hasNativeReturnTypehint;
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
