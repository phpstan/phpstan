<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\Traits\FalseyBooleanTypeTrait;
use PHPStan\Type\Traits\NonCallableTypeTrait;
use PHPStan\Type\Traits\NonGenericTypeTrait;
use PHPStan\Type\Traits\NonIterableTypeTrait;
use PHPStan\Type\Traits\NonObjectTypeTrait;

class NullType implements ConstantScalarType
{

	use NonCallableTypeTrait;
	use NonIterableTypeTrait;
	use NonObjectTypeTrait;
	use FalseyBooleanTypeTrait;
	use NonGenericTypeTrait;

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

	public function accepts(Type $type, bool $strictTypes): TrinaryLogic
	{
		if ($type instanceof self) {
			return TrinaryLogic::createYes();
		}

		if ($type instanceof CompoundType) {
			return CompoundTypeHelper::accepts($type, $this, $strictTypes);
		}

		return TrinaryLogic::createNo();
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

	public function equals(Type $type): bool
	{
		return $type instanceof self;
	}

	public function describe(VerbosityLevel $level): string
	{
		return 'null';
	}

	public function toNumber(): Type
	{
		return new ConstantIntegerType(0);
	}

	public function toString(): Type
	{
		return new ConstantStringType('');
	}

	public function toInteger(): Type
	{
		return $this->toNumber();
	}

	public function toFloat(): Type
	{
		return $this->toNumber()->toFloat();
	}

	public function toArray(): Type
	{
		return new ConstantArrayType([], []);
	}

	public function isOffsetAccessible(): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public function hasOffsetValueType(Type $offsetType): TrinaryLogic
	{
		return TrinaryLogic::createYes();
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

	public function traverse(callable $cb): Type
	{
		return $this;
	}

	public function isArray(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	/**
	 * @param mixed[] $properties
	 * @return Type
	 */
	public static function __set_state(array $properties): Type
	{
		return new self();
	}

}
