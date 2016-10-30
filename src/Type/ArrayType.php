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

	public static function createDeepArrayType(Type $itemType, bool $nullable, int $depth): self
	{
		for ($i = 0; $i < $depth - 1; $i++) {
			$itemType = new ArrayType($itemType, false);
		}

		return new ArrayType($itemType, $nullable);
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

	public function equals(Type $type): bool
	{
		if ($type instanceof self) {
			return $this->getItemType()->equals($type->getItemType()) && $this->isNullable() === $type->isNullable();
		}

		return false;
	}

}
