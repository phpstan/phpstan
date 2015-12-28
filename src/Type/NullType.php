<?php declare(strict_types=1);

namespace PHPStan\Type;

class NullType implements Type
{

	public function getClass()
	{
		return null;
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

}
