<?php declare(strict_types = 1);

namespace PHPStan\Type;

class NullType implements Type
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

	public function isNullable(): bool
	{
		return true;
	}

	public function combineWith(Type $otherType): Type
	{
		return $otherType->makeNullable();
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
		return 'null';
	}

	public function canAccessProperties(): bool
	{
		return false;
	}

	public function canCallMethods(): bool
	{
		return false;
	}

	public function isDocumentableNatively(): bool
	{
		return true;
	}

}
