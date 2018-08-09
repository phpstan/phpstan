<?php declare(strict_types = 1);

namespace PHPStan\Type\Accessory;

use PHPStan\TrinaryLogic;
use PHPStan\Type\CompoundType;
use PHPStan\Type\CompoundTypeHelper;
use PHPStan\Type\ConstantScalarType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Traits\MaybeCallableTypeTrait;
use PHPStan\Type\Traits\MaybeIterableTypeTrait;
use PHPStan\Type\Traits\MaybeObjectTypeTrait;
use PHPStan\Type\Traits\UndecidedBooleanTypeTrait;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

class HasOffsetType implements CompoundType, AccessoryType
{

	use MaybeCallableTypeTrait;
	use MaybeIterableTypeTrait;
	use MaybeObjectTypeTrait;
	use UndecidedBooleanTypeTrait;

	/** @var \PHPStan\Type\Type */
	private $offsetType;

	public function __construct(Type $offsetType)
	{
		$this->offsetType = $offsetType;
	}

	public function getReferencedClasses(): array
	{
		return [];
	}

	public function accepts(Type $type, bool $strictTypes): TrinaryLogic
	{
		if ($type instanceof CompoundType) {
			return CompoundTypeHelper::accepts($type, $this, $strictTypes);
		}

		return $type->isOffsetAccessible()
			->and($type->hasOffsetValueType($this->offsetType));
	}

	public function isSuperTypeOf(Type $type): TrinaryLogic
	{
		if ($this->equals($type)) {
			return TrinaryLogic::createYes();
		}
		return $type->isOffsetAccessible()
			->and($type->hasOffsetValueType($this->offsetType));
	}

	public function isSubTypeOf(Type $otherType): TrinaryLogic
	{
		if ($otherType instanceof UnionType || $otherType instanceof IntersectionType) {
			return $otherType->isSuperTypeOf($this);
		}

		return $otherType->isOffsetAccessible()
			->and($otherType->hasOffsetValueType($this->offsetType))
			->and($otherType instanceof self ? TrinaryLogic::createYes() : TrinaryLogic::createMaybe());
	}

	public function equals(Type $type): bool
	{
		return $type instanceof self
			&& $this->offsetType->equals($type->offsetType);
	}

	public function describe(\PHPStan\Type\VerbosityLevel $level): string
	{
		return sprintf('hasOffset(%s)', $this->offsetType->describe($level));
	}

	public function isOffsetAccessible(): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public function hasOffsetValueType(Type $offsetType): TrinaryLogic
	{
		if ($offsetType instanceof ConstantScalarType && $offsetType->equals($this->offsetType)) {
			return TrinaryLogic::createYes();
		}

		return TrinaryLogic::createMaybe();
	}

	public function getOffsetValueType(Type $offsetType): Type
	{
		return new MixedType();
	}

	public function setOffsetValueType(?Type $offsetType, Type $valueType): Type
	{
		return $this;
	}

	public function toNumber(): Type
	{
		return new ErrorType();
	}

	public function toInteger(): Type
	{
		return new ErrorType();
	}

	public function toFloat(): Type
	{
		return new ErrorType();
	}

	public function toString(): Type
	{
		return new ErrorType();
	}

	public function toArray(): Type
	{
		return new MixedType();
	}

	public static function __set_state(array $properties): Type
	{
		return new self($properties['offsetType']);
	}

}
