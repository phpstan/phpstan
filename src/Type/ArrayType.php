<?php declare(strict_types = 1);

namespace PHPStan\Type;

class ArrayType implements IterableType
{

	use IterableTypeTrait;

	/** @var bool */
	private $itemTypeInferredFromLiteralArray;

	/** @var bool */
	private $possiblyCallable;

	public function __construct(
		Type $itemType,
		bool $nullable,
		bool $itemTypeInferredFromLiteralArray = false,
		bool $possiblyCallable = false
	)
	{
		$this->itemType = $itemType;
		$this->nullable = $nullable;
		$this->itemTypeInferredFromLiteralArray = $itemTypeInferredFromLiteralArray;
		$this->possiblyCallable = $possiblyCallable;
	}

	public static function createDeepArrayType(NestedArrayItemType $nestedItemType, bool $nullable): self
	{
		$itemType = $nestedItemType->getItemType();
		for ($i = 0; $i < $nestedItemType->getDepth() - 1; $i++) {
			$itemType = new self($itemType, false);
		}

		return new self($itemType, $nullable);
	}

	public function isItemTypeInferredFromLiteralArray(): bool
	{
		return $this->itemTypeInferredFromLiteralArray;
	}

	public function isPossiblyCallable(): bool
	{
		return $this->possiblyCallable;
	}

	public function combineWith(Type $otherType): Type
	{
		if ($otherType instanceof IterableType) {
			$isItemInferredFromLiteralArray = $this->isItemTypeInferredFromLiteralArray();
			$isPossiblyCallable = $this->isPossiblyCallable();
			if ($otherType instanceof self) {
				$isItemInferredFromLiteralArray = $isItemInferredFromLiteralArray || $otherType->isItemTypeInferredFromLiteralArray();
				$isPossiblyCallable = $isPossiblyCallable || $otherType->isPossiblyCallable();
			}
			return new self(
				$this->getItemType()->combineWith($otherType->getItemType()),
				$this->isNullable() || $otherType->isNullable(),
				$isItemInferredFromLiteralArray,
				$isPossiblyCallable
			);
		}

		if ($otherType instanceof NullType) {
			return $this->makeNullable();
		}

		return new MixedType($this->isNullable() || $otherType->isNullable());
	}

	public function makeNullable(): Type
	{
		return new self($this->getItemType(), true, $this->isItemTypeInferredFromLiteralArray(), $this->isPossiblyCallable());
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

}
