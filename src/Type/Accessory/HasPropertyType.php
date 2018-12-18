<?php declare(strict_types = 1);

namespace PHPStan\Type\Accessory;

use PHPStan\Reflection\ClassMemberAccessAnswerer;
use PHPStan\TrinaryLogic;
use PHPStan\Type\CompoundType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\Traits\TruthyBooleanTypeTrait;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

class HasPropertyType extends ObjectWithoutClassType implements CompoundType, AccessoryType
{

	use TruthyBooleanTypeTrait;

	/** @var string */
	private $propertyName;

	public function __construct(string $propertyName)
	{
		$this->propertyName = $propertyName;
	}

	public function getPropertyName(): string
	{
		return $this->propertyName;
	}

	public function accepts(Type $type, bool $strictTypes): TrinaryLogic
	{
		return TrinaryLogic::createFromBoolean($this->equals($type));
	}

	public function isSuperTypeOf(Type $type): TrinaryLogic
	{
		return $type->hasProperty($this->propertyName);
	}

	public function isSubTypeOf(Type $otherType): TrinaryLogic
	{
		if ($otherType instanceof UnionType || $otherType instanceof IntersectionType) {
			return $otherType->isSuperTypeOf($this);
		}

		if ($otherType instanceof self) {
			$limit = TrinaryLogic::createYes();
		} else {
			$limit = TrinaryLogic::createMaybe();
		}

		return $limit->and($otherType->hasProperty($this->propertyName));
	}

	public function equals(Type $type): bool
	{
		return $type instanceof self
			&& $this->propertyName === $type->propertyName;
	}

	public function describe(\PHPStan\Type\VerbosityLevel $level): string
	{
		return sprintf('hasProperty(%s)', $this->propertyName);
	}

	public function hasProperty(string $propertyName): TrinaryLogic
	{
		return TrinaryLogic::createFromBoolean(
			$this->propertyName === $propertyName
		);
	}

	public function isIterable(): TrinaryLogic
	{
		return TrinaryLogic::createMaybe();
	}

	public function isIterableAtLeastOnce(): TrinaryLogic
	{
		return TrinaryLogic::createMaybe();
	}

	public function getIterableKeyType(): Type
	{
		return new MixedType();
	}

	public function getIterableValueType(): Type
	{
		return new MixedType();
	}

	public function isOffsetAccessible(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function hasOffsetValueType(Type $offsetType): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function getOffsetValueType(Type $offsetType): Type
	{
		return new ErrorType();
	}

	public function setOffsetValueType(?Type $offsetType, Type $valueType): Type
	{
		return new ErrorType();
	}

	public function isCallable(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function getCallableParametersAcceptors(ClassMemberAccessAnswerer $scope): array
	{
		throw new \PHPStan\ShouldNotHappenException();
	}

	public function toString(): Type
	{
		return new ErrorType();
	}

	public static function __set_state(array $properties): Type
	{
		return new self($properties['propertyName']);
	}

}
