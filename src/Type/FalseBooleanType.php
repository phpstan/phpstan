<?php declare(strict_types = 1);

namespace PHPStan\Type;

class FalseBooleanType implements BooleanType
{

	public function describe(): string
	{
		return 'false';
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
			return $this;
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

	public function isIterable(): int
	{
		return self::RESULT_NO;
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
