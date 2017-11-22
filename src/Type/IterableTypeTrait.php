<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassConstantReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\PropertyReflection;

trait IterableTypeTrait
{

	/** @var \PHPStan\Type\Type */
	private $itemType;

	public function getItemType(): Type
	{
		return $this->itemType;
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

	public function isClonable(): bool
	{
		return false;
	}

}
