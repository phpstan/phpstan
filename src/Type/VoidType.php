<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Traits\NonCallableTypeTrait;
use PHPStan\Type\Traits\NonIterableTypeTrait;
use PHPStan\Type\Traits\NonObjectTypeTrait;
use PHPStan\Type\Traits\NonOffsetAccessibleTypeTrait;

class VoidType implements Type
{

	use NonCallableTypeTrait;
	use NonIterableTypeTrait;
	use NonObjectTypeTrait;
	use NonOffsetAccessibleTypeTrait;

	/**
	 * @return string[]
	 */
	public function getReferencedClasses(): array
	{
		return [];
	}

	public function accepts(Type $type): bool
	{
		return $type instanceof self;
	}

	public function isSuperTypeOf(Type $type): TrinaryLogic
	{
		if ($type instanceof self) {
			return TrinaryLogic::createYes();
		}

		if ($type instanceof CompoundType) {
			return $type->isSubTypeOf($this);
		}

		return TrinaryLogic::createNo();
	}

	public function describe(): string
	{
		return 'void';
	}

	public static function __set_state(array $properties): Type
	{
		return new self();
	}

}
