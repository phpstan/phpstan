<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Traits\NonCallableTypeTrait;
use PHPStan\Type\Traits\NonIterableTypeTrait;
use PHPStan\Type\Traits\NonObjectTypeTrait;
use PHPStan\Type\Traits\NonOffsetAccessibleTypeTrait;

class NullType implements ConstantScalarType
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

	/**
	 * @return null
	 */
	public function getValue()
	{
		return null;
	}

	public function generalize(): Type
	{
		return $this;
	}

	public function accepts(Type $type): bool
	{
		if ($type instanceof self) {
			return true;
		}

		if ($type instanceof CompoundType) {
			return CompoundTypeHelper::accepts($type, $this);
		}

		return false;
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
		return 'null';
	}

	public function getOffsetValueType(Type $offsetType): Type
	{
		return new NullType();
	}

	public function setOffsetValueType(?Type $offsetType, Type $valueType): Type
	{
		$array = new ConstantArrayType([], []);
		return $array->setOffsetValueType($offsetType, $valueType);
	}

	public static function __set_state(array $properties): Type
	{
		return new self();
	}

}
