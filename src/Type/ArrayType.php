<?php declare(strict_types = 1);

namespace PHPStan\Type;

class ArrayType implements Type
{

	/** @var \PHPStan\Type\Type */
	private $itemType;

	/** @var bool */
	private $nullable;

	/** @var bool */
	private $itemTypeInferredFromLiteralArray;

	public function __construct(Type $itemType, bool $nullable, bool $itemTypeInferredFromLiteralArray = false)
	{
		$this->itemType = $itemType;
		$this->nullable = $nullable;
		$this->itemTypeInferredFromLiteralArray = $itemTypeInferredFromLiteralArray;
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

	public function isItemTypeInferredFromLiteralArray(): bool
	{
		return $this->itemTypeInferredFromLiteralArray;
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
			return new self(
				$this->getItemType()->combineWith($otherType->getItemType()),
				$this->isNullable() || $otherType->isNullable(),
				$this->isItemTypeInferredFromLiteralArray() || $otherType->isItemTypeInferredFromLiteralArray()
			);
		}

		if ($otherType instanceof NullType) {
			return $this->makeNullable();
		}

		return new MixedType($this->isNullable() || $otherType->isNullable());
	}

	public function makeNullable(): Type
	{
		return new self($this->getItemType(), true, $this->isItemTypeInferredFromLiteralArray());
	}

	public function accepts(Type $type): bool
	{
		if ($type instanceof self) {
			return $this->getItemType()->accepts($type->getItemType());
		}

		if ($type instanceof MixedType) {
			return true;
		}

		if ($this->isNullable() && $type instanceof NullType) {
			return true;
		}

		return false;
	}

	public function describe(): string
	{
		return sprintf('%s[]', $this->getItemType()->describe());
	}

	public function canAccessProperties(): bool
	{
		return false;
	}

	public function canCallMethods(): bool
	{
		return false;
	}

}
