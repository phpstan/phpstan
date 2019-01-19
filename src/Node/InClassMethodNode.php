<?php declare(strict_types = 1);

namespace PHPStan\Node;

class InClassMethodNode extends \PhpParser\Node\Stmt implements VirtualNode
{

	/** @var \PhpParser\Node\Stmt\ClassMethod */
	private $originalNode;

	public function __construct(\PhpParser\Node\Stmt\ClassMethod $originalNode)
	{
		parent::__construct($originalNode->getAttributes());
		$this->originalNode = $originalNode;
	}

	public function getOriginalNode(): \PhpParser\Node\Stmt\ClassMethod
	{
		return $this->originalNode;
	}

	public function getType(): string
	{
		return 'PHPStan_Stmt_InClassMethodNode';
	}

	public function getSubNodeNames(): array
	{
		return [];
	}

}
