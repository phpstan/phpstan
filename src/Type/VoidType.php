<?php declare(strict_types = 1);

namespace PHPStan\Type;

class VoidType implements Type
{

	/**
	 * @return string|null
	 */
	public function getClass()
	{
		return null;
	}

	public function isNullable(): bool
	{
		return false;
	}

	public function combineWith(Type $otherType): Type
	{
		if ($otherType instanceof self) {
			return $this;
		}

		return new MixedType(true);
	}

	public function makeNullable(): Type
	{
		return $this;
	}

	public function accepts(Type $type): bool
	{
		return $type instanceof self || $type instanceof MixedType;
	}

	public function describe(): string
	{
		return 'void';
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
