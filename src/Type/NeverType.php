<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassConstantReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Reflection\TrivialParametersAcceptor;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Traits\FalseyBooleanTypeTrait;

class NeverType implements CompoundType
{

	use FalseyBooleanTypeTrait;

	/**
	 * @return string[]
	 */
	public function getReferencedClasses(): array
	{
		return [];
	}

	public function accepts(Type $type): bool
	{
		return true;
	}

	public function isSuperTypeOf(Type $type): TrinaryLogic
	{
		if ($type instanceof self) {
			return TrinaryLogic::createYes();
		}

		return TrinaryLogic::createNo();
	}

	public function isSubTypeOf(Type $otherType): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public function describe(VerbosityLevel $level): string
	{
		return '*NEVER*';
	}

	public function canAccessProperties(): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public function hasProperty(string $propertyName): bool
	{
		return false;
	}

	public function getProperty(string $propertyName, Scope $scope): PropertyReflection
	{
		throw new \PHPStan\ShouldNotHappenException();
	}

	public function canCallMethods(): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public function hasMethod(string $methodName): bool
	{
		return false;
	}

	public function getMethod(string $methodName, Scope $scope): MethodReflection
	{
		throw new \PHPStan\ShouldNotHappenException();
	}

	public function canAccessConstants(): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public function hasConstant(string $constantName): bool
	{
		return false;
	}

	public function getConstant(string $constantName): ClassConstantReflection
	{
		throw new \PHPStan\ShouldNotHappenException();
	}

	public function isIterable(): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public function getIterableKeyType(): Type
	{
		return new NeverType();
	}

	public function getIterableValueType(): Type
	{
		return new NeverType();
	}

	public function isOffsetAccessible(): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public function getOffsetValueType(Type $offsetType): Type
	{
		return new NeverType();
	}

	public function setOffsetValueType(?Type $offsetType, Type $valueType): Type
	{
		return new NeverType();
	}

	public function isCallable(): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public function getCallableParametersAcceptor(Scope $scope): ParametersAcceptor
	{
		return new TrivialParametersAcceptor();
	}

	public function isCloneable(): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public function toNumber(): Type
	{
		return $this;
	}

	public function toString(): Type
	{
		return $this;
	}

	public function toInteger(): Type
	{
		return $this;
	}

	public function toFloat(): Type
	{
		return $this;
	}

	public function toArray(): Type
	{
		return $this;
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
