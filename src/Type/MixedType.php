<?php declare(strict_types = 1);

namespace PHPStan\Type;

class MixedType implements Type
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
		return $this;
	}

	public function makeNullable(): Type
	{
		return $this;
	}

	public function accepts(Type $type): bool
	{
		return true;
	}

	public function describe(): string
	{
		return 'mixed';
	}

	public function canAccessProperties(): bool
	{
		return true;
	}

	public function canCallMethods(): bool
	{
		return true;
	}

	public function isDocumentableNatively(): bool
	{
		return true;
	}

}
