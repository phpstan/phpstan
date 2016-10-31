<?php declare(strict_types = 1);

namespace PHPStan\Type;

class NestedArrayItemType
{

	/** @var \PHPStan\Type\Type */
	private $itemType;

	/** @var int */
	private $depth;

	public function __construct(Type $itemType, int $depth)
	{
		$this->itemType = $itemType;
		$this->depth = $depth;
	}

	public function getItemType(): Type
	{
		return $this->itemType;
	}

	public function getDepth(): int
	{
		return $this->depth;
	}

}
