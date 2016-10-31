<?php declare(strict_types = 1);

namespace PHPStan\Type;

class ArrayType implements Type
{

	/** @var \PHPStan\Type\Type */
	private $itemType;

	/** @var bool */
	private $nullable;

	public function __construct(Type $itemType, bool $nullable)
	{
		$this->itemType = $itemType;
		$this->nullable = $nullable;
	}

	public static function createDeepArrayType(NestedArrayItemType $nestedItemType, bool $nullable): self
	{
		$itemType = $nestedItemType->getItemType();
		for ($i = 0; $i < $nestedItemType->getDepth() - 1; $i++) {
			$itemType = new ArrayType($itemType, false);
		}

		return new ArrayType($itemType, $nullable);
	}

	public function getNestedItemType(): NestedArrayItemType
	{
		$depth = 0;
		$itemType = $this;
		while ($itemType instanceof ArrayType) {
			$itemType = $itemType->getItemType();
			$depth++;
		}

		return new NestedArrayItemType($itemType, $depth);
	}

	public function getItemType(): Type
	{
		return $this->itemType;
	}

	/**
	 * @return string|null
	 */
	public function getClass()
	{
		return null;
	}

	public function isNullable(): bool
	{
		return $this->nullable;
	}

	public function combineWith(Type $otherType): Type
	{
		if ($otherType instanceof ArrayType) {
			return new self($this->getItemType()->combineWith($otherType->getItemType()), $this->isNullable() || $otherType->isNullable());
		}

		if ($otherType instanceof NullType) {
			return $this->makeNullable();
		}

		return new MixedType($this->isNullable() || $otherType->isNullable());
	}

	public function makeNullable(): Type
	{
		return new self($this->getItemType(), true);
	}

}
