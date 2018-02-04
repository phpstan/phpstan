<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Traits\MaybeIterableTypeTrait;
use PHPStan\Type\Traits\MaybeObjectTypeTrait;
use PHPStan\Type\Traits\MaybeOffsetAccessibleTypeTrait;

class CallableType implements CompoundType
{

	use MaybeIterableTypeTrait;
	use MaybeObjectTypeTrait;
	use MaybeOffsetAccessibleTypeTrait;

	/**
	 * @return string[]
	 */
	public function getReferencedClasses(): array
	{
		return [];
	}

	public function accepts(Type $type): bool
	{
		if ($type instanceof CompoundType) {
			return CompoundTypeHelper::accepts($type, $this);
		}

		return $type->isCallable()->yes() || $type instanceof StringType;
	}

	public function isSuperTypeOf(Type $type): TrinaryLogic
	{
		return $type->isCallable();
	}

	public function isSubTypeOf(Type $otherType): TrinaryLogic
	{
		if ($otherType instanceof IntersectionType || $otherType instanceof UnionType) {
			return $otherType->isSuperTypeOf($this);
		}

		return $otherType->isCallable()
			->and($otherType instanceof self ? TrinaryLogic::createYes() : TrinaryLogic::createMaybe());
	}

	public function describe(): string
	{
		return 'callable';
	}

	public function isCallable(): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public static function __set_state(array $properties): Type
	{
		return new self();
	}

}
