<?php declare(strict_types = 1);

namespace PHPStan\Type\Traits;

use PHPStan\Reflection\ClassMemberAccessAnswerer;
use PHPStan\Reflection\ConstantReflection;
use PHPStan\Reflection\Dummy\DummyConstantReflection;
use PHPStan\Reflection\Dummy\DummyMethodReflection;
use PHPStan\Reflection\Dummy\DummyPropertyReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\TrinaryLogic;

trait MaybeObjectTypeTrait
{

	public function canAccessProperties(): TrinaryLogic
	{
		return TrinaryLogic::createMaybe();
	}

	public function hasProperty(string $propertyName): TrinaryLogic
	{
		return TrinaryLogic::createMaybe();
	}

	public function getProperty(string $propertyName, ClassMemberAccessAnswerer $scope): PropertyReflection
	{
		return new DummyPropertyReflection();
	}

	public function canCallMethods(): TrinaryLogic
	{
		return TrinaryLogic::createMaybe();
	}

	public function hasMethod(string $methodName): TrinaryLogic
	{
		return TrinaryLogic::createMaybe();
	}

	public function getMethod(string $methodName, ClassMemberAccessAnswerer $scope): MethodReflection
	{
		return new DummyMethodReflection($methodName);
	}

	public function canAccessConstants(): TrinaryLogic
	{
		return TrinaryLogic::createMaybe();
	}

	public function hasConstant(string $constantName): TrinaryLogic
	{
		return TrinaryLogic::createMaybe();
	}

	public function getConstant(string $constantName): ConstantReflection
	{
		return new DummyConstantReflection($constantName);
	}

	public function isCloneable(): TrinaryLogic
	{
		return TrinaryLogic::createMaybe();
	}

}
