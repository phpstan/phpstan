<?php declare(strict_types = 1);

namespace PHPStan\Node;

class InFunctionNode extends \PhpParser\Node\Stmt implements VirtualNode
{

	/** @var \PhpParser\Node\Stmt\Function_ */
	private $originalNode;

	public function __construct(\PhpParser\Node\Stmt\Function_ $originalNode)
	{
		parent::__construct($originalNode->getAttributes());
		$this->originalNode = $originalNode;
	}

	public function getOriginalNode(): \PhpParser\Node\Stmt\Function_
	{
		return $this->originalNode;
	}

	public function getType(): string
	{
		return 'PHPStan_Stmt_InFunctionNode';
	}

	/**
	 * @return string[]
	 */
	public function getSubNodeNames(): array
	{
		return [];
	}

}
