<?php declare(strict_types = 1);

namespace PHPStan\Type;

class TrueBooleanType implements BooleanType
{

	public function describe(): string
	{
		return 'true';
	}

	public function canAccessProperties(): bool
	{
		return false;
	}

	public function canCallMethods(): bool
	{
		return false;
	}

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
		if ($otherType instanceof self) {
			return new self();
		}
		if ($otherType instanceof BooleanType) {
			return new TrueOrFalseBooleanType();
		}

		return TypeCombinator::combine($this, $otherType);
	}

	public function accepts(Type $type): bool
	{
		if ($type instanceof self) {
			return true;
		}

		return $type instanceof MixedType;
	}

	public function isDocumentableNatively(): bool
	{
		return true;
	}

}
