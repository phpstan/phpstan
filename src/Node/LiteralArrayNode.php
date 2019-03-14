<?php declare(strict_types = 1);

namespace PHPStan\Node;

use PhpParser\Node\Expr\Array_;
use PhpParser\NodeAbstract;

class LiteralArrayNode extends NodeAbstract implements VirtualNode
{

	/** @var LiteralArrayItem[] */
	private $itemNodes;

	/**
	 * @param Array_ $originalNode
	 * @param LiteralArrayItem[] $itemNodes
	 */
	public function __construct(Array_ $originalNode, array $itemNodes)
	{
		parent::__construct($originalNode->getAttributes());
		$this->itemNodes = $itemNodes;
	}

	/**
	 * @return LiteralArrayItem[]
	 */
	public function getItemNodes(): array
	{
		return $this->itemNodes;
	}

	public function getType(): string
	{
		return 'PHPStan_Node_LiteralArray';
	}

	public function getSubNodeNames(): array
	{
		return [];
	}

}
