<?php declare(strict_types = 1);

namespace PHPStan\Node;

use PhpParser\Node\Expr\Closure;
use PhpParser\NodeAbstract;

class InClosureNode extends NodeAbstract implements VirtualNode
{

	/** @var \PhpParser\Node\Expr\Closure */
	private $originalNode;

	public function __construct(Closure $originalNode)
	{
		parent::__construct($originalNode->getAttributes());
		$this->originalNode = $originalNode;
	}

	public function getOriginalNode(): Closure
	{
		return $this->originalNode;
	}

	public function getType(): string
	{
		return 'PHPStan_Node_InClosureNode';
	}

	public function getSubNodeNames(): array
	{
		return [];
	}

}
