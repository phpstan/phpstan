<?php declare(strict_types = 1);

namespace PHPStan\Node;

use PhpParser\Node\Stmt;

class UnreachableStatementNode extends Stmt implements VirtualNode
{

	/** @var Stmt */
	private $originalStatement;

	public function __construct(Stmt $originalStatement)
	{
		parent::__construct($originalStatement->getAttributes());
		$this->originalStatement = $originalStatement;
	}

	public function getOriginalStatement(): Stmt
	{
		return $this->originalStatement;
	}

	public function getType(): string
	{
		return 'PHPStan_Stmt_UnreachableStatementNode';
	}

	public function getSubNodeNames(): array
	{
		return [];
	}

}
