<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassConstantReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\TrinaryLogic;

class ObjectWithoutClassType implements Type
{

	public function hasProperty(string $propertyName): bool
	{
		return false;
	}

	public function getProperty(string $propertyName, Scope $scope): PropertyReflection
	{
		throw new \PHPStan\ShouldNotHappenException();
	}

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

		return $type instanceof self || $type instanceof TypeWithClassName;
	}

	public function isSuperTypeOf(Type $type): TrinaryLogic
	{
		if ($type instanceof CompoundType) {
			return $type->isSubTypeOf($this);
		}

		if ($type instanceof self || $type instanceof TypeWithClassName) {
			return TrinaryLogic::createYes();
		}

		return TrinaryLogic::createNo();
	}

	public function describe(): string
	{
		return 'object';
	}

	public function canAccessProperties(): TrinaryLogic
	{
		return TrinaryLogic::createYes();
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

	public function isOffsetAccesible(): TrinaryLogic
	{
		return TrinaryLogic::createMaybe();
	}

	public function getOffsetValueType(): Type
	{
		return new MixedType();
	}

	public function isCallable(): TrinaryLogic
	{
		return TrinaryLogic::createMaybe();
	}

	public function isCloneable(): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public static function __set_state(array $properties): Type
	{
		return new self();
	}

}
