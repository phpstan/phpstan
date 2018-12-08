<?php declare(strict_types = 1);

namespace PHPStan\Type\Generic;

use PHPStan\Reflection\ClassMemberAccessAnswerer;
use PHPStan\Reflection\ConstantReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

class TemplateType implements Type
{

	/** @var string */
	private $identifier;

	public function __construct(string $identifier)
	{
		$this->identifier = $identifier;
	}

	public function getReferencedClasses(): array
	{
		return [];
	}

	public function accepts(Type $type, bool $strictTypes): TrinaryLogic
	{
		// TODO: Implement accepts() method.
	}

	public function isSuperTypeOf(Type $type): TrinaryLogic
	{
		// TODO: Implement isSuperTypeOf() method.
	}

	public function equals(Type $type): bool
	{
		// TODO: Implement equals() method.
	}

	public function describe(VerbosityLevel $level): string
	{
		return $this->identifier;
	}

	public function canAccessProperties(): TrinaryLogic
	{
		// TODO: Implement canAccessProperties() method.
	}

	public function hasProperty(string $propertyName): TrinaryLogic
	{
		// TODO: Implement hasProperty() method.
	}

	public function getProperty(string $propertyName, ClassMemberAccessAnswerer $scope): PropertyReflection
	{
		// TODO: Implement getProperty() method.
	}

	public function canCallMethods(): TrinaryLogic
	{
		// TODO: Implement canCallMethods() method.
	}

	public function hasMethod(string $methodName): TrinaryLogic
	{
		// TODO: Implement hasMethod() method.
	}

	public function getMethod(string $methodName, ClassMemberAccessAnswerer $scope): MethodReflection
	{
		// TODO: Implement getMethod() method.
	}

	public function canAccessConstants(): TrinaryLogic
	{
		// TODO: Implement canAccessConstants() method.
	}

	public function hasConstant(string $constantName): TrinaryLogic
	{
		// TODO: Implement hasConstant() method.
	}

	public function getConstant(string $constantName): ConstantReflection
	{
		// TODO: Implement getConstant() method.
	}

	public function isIterable(): TrinaryLogic
	{
		// TODO: Implement isIterable() method.
	}

	public function isIterableAtLeastOnce(): TrinaryLogic
	{
		// TODO: Implement isIterableAtLeastOnce() method.
	}

	public function getIterableKeyType(): Type
	{
		// TODO: Implement getIterableKeyType() method.
	}

	public function getIterableValueType(): Type
	{
		// TODO: Implement getIterableValueType() method.
	}

	public function isOffsetAccessible(): TrinaryLogic
	{
		// TODO: Implement isOffsetAccessible() method.
	}

	public function hasOffsetValueType(Type $offsetType): TrinaryLogic
	{
		// TODO: Implement hasOffsetValueType() method.
	}

	public function getOffsetValueType(Type $offsetType): Type
	{
		// TODO: Implement getOffsetValueType() method.
	}

	public function setOffsetValueType(?Type $offsetType, Type $valueType): Type
	{
		// TODO: Implement setOffsetValueType() method.
	}

	public function isCallable(): TrinaryLogic
	{
		// TODO: Implement isCallable() method.
	}

	public function getCallableParametersAcceptors(ClassMemberAccessAnswerer $scope): array
	{
		// TODO: Implement getCallableParametersAcceptors() method.
	}

	public function isCloneable(): TrinaryLogic
	{
		// TODO: Implement isCloneable() method.
	}

	public function toBoolean(): BooleanType
	{
		// TODO: Implement toBoolean() method.
	}

	public function toNumber(): Type
	{
		// TODO: Implement toNumber() method.
	}

	public function toInteger(): Type
	{
		// TODO: Implement toInteger() method.
	}

	public function toFloat(): Type
	{
		// TODO: Implement toFloat() method.
	}

	public function toString(): Type
	{
		// TODO: Implement toString() method.
	}

	public function toArray(): Type
	{
		// TODO: Implement toArray() method.
	}

	public static function __set_state(array $properties): Type
	{
		return new self($properties['identifier']);
	}

}
