<?php declare(strict_types = 1);

namespace PHPStan\Type;

trait JustNullableTypeTrait
{

	/**
	 * @return string|null
	 */
	public function getClass()
	{
		return null;
	}

	/**
	 * @return string[]
	 */
	public function getReferencedClasses(): array
	{
		return [];
	}

	public function combineWith(Type $otherType): Type
	{
		if ($otherType instanceof $this) {
			return new self();
		}

		/** @var \PHPStan\Type\Type $thisType */
		$thisType = $this;
		return TypeCombinator::combine($thisType, $otherType);
	}

	public function accepts(Type $type): bool
	{
		if ($type instanceof $this) {
			return true;
		}

		if ($type instanceof CompoundType) {
			return CompoundTypeHelper::accepts($type, $this);
		}

		return false;
	}

	public function isDocumentableNatively(): bool
	{
		return true;
	}

	public function isIterable(): int
	{
		return TrinaryLogic::NO;
	}

	public function getIterableKeyType(): Type
	{
		return new ErrorType();
	}

	public function getIterableValueType(): Type
	{
		return new ErrorType();
	}

}
