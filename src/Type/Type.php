<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\TrinaryLogic;

interface Type
{

	/**
	 * @return string|null
	 */
	public function getClass();

	/**
	 * @return string[]
	 */
	public function getReferencedClasses(): array;

	public function accepts(Type $type): bool;

	public function isSupersetOf(Type $type): TrinaryLogic;

	public function describe(): string;

	public function canAccessProperties(): bool;

	public function hasProperty(string $propertyName): bool;

	public function getProperty(string $propertyName, Scope $scope): PropertyReflection;

	public function canCallMethods(): bool;

	public function hasMethod(string $methodName): bool;

	public function getMethod(string $methodName, Scope $scope): MethodReflection;

	public function isDocumentableNatively(): bool;

	public function isIterable(): TrinaryLogic;

	public function getIterableKeyType(): Type;

	public function getIterableValueType(): Type;

	public function isCallable(): TrinaryLogic;

	public static function __set_state(array $properties): self;

}
