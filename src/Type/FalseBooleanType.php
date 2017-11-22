<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassConstantReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\TrinaryLogic;

class FalseBooleanType implements BooleanType
{

	public function describe(): string
	{
		return 'false';
	}

	public function canAccessProperties(): bool
	{
		return false;
	}

	public function hasProperty(string $propertyName): bool
	{
		return false;
	}

	public function getProperty(string $propertyName, Scope $scope): PropertyReflection
	{
		throw new \PHPStan\ShouldNotHappenException();
	}

	public function canCallMethods(): bool
	{
		return false;
	}

	public function hasMethod(string $methodName): bool
	{
		return false;
	}

	public function getMethod(string $methodName, Scope $scope): MethodReflection
	{
		throw new \PHPStan\ShouldNotHappenException();
	}

	public function canAccessConstants(): bool
	{
		return false;
	}

	public function hasConstant(string $constantName): bool
	{
		return false;
	}

	public function getConstant(string $constantName): ClassConstantReflection
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

		if ($type instanceof TrueOrFalseBooleanType) {
			return TrinaryLogic::createMaybe();
		}

		if ($type instanceof CompoundType) {
			return $type->isSubTypeOf($this);
		}

		return TrinaryLogic::createNo();
	}

	public function isDocumentableNatively(): bool
	{
		return true;
	}

	public function isIterable(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function getIterableKeyType(): Type
	{
		return new ErrorType();
	}

	public function getIterableValueType(): Type
	{
		return new ErrorType();
	}

	public function isCallable(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function isClonable(): bool
	{
		return false;
	}

	public static function __set_state(array $properties): Type
	{
		return new self();
	}

}
