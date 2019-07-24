<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Reflection\ClassMemberAccessAnswerer;
use PHPStan\Reflection\ConstantReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Generic\TemplateTypeMap;

interface Type
{

	/**
	 * @return string[]
	 */
	public function getReferencedClasses(): array;

	public function accepts(Type $type, bool $strictTypes): TrinaryLogic;

	public function isSuperTypeOf(Type $type): TrinaryLogic;

	public function equals(Type $type): bool;

	public function describe(VerbosityLevel $level): string;

	public function canAccessProperties(): TrinaryLogic;

	public function hasProperty(string $propertyName): TrinaryLogic;

	public function getProperty(string $propertyName, ClassMemberAccessAnswerer $scope): PropertyReflection;

	public function canCallMethods(): TrinaryLogic;

	public function hasMethod(string $methodName): TrinaryLogic;

	public function getMethod(string $methodName, ClassMemberAccessAnswerer $scope): MethodReflection;

	public function canAccessConstants(): TrinaryLogic;

	public function hasConstant(string $constantName): TrinaryLogic;

	public function getConstant(string $constantName): ConstantReflection;

	public function isIterable(): TrinaryLogic;

	public function isIterableAtLeastOnce(): TrinaryLogic;

	public function getIterableKeyType(): Type;

	public function getIterableValueType(): Type;

	public function isArray(): TrinaryLogic;

	public function isOffsetAccessible(): TrinaryLogic;

	public function hasOffsetValueType(Type $offsetType): TrinaryLogic;

	public function getOffsetValueType(Type $offsetType): Type;

	public function setOffsetValueType(?Type $offsetType, Type $valueType): Type;

	public function isCallable(): TrinaryLogic;

	/**
	 * @param \PHPStan\Reflection\ClassMemberAccessAnswerer $scope
	 * @return \PHPStan\Reflection\ParametersAcceptor[]
	 */
	public function getCallableParametersAcceptors(ClassMemberAccessAnswerer $scope): array;

	public function isCloneable(): TrinaryLogic;

	public function toBoolean(): BooleanType;

	public function toNumber(): Type;

	public function toInteger(): Type;

	public function toFloat(): Type;

	public function toString(): Type;

	public function toArray(): Type;

	/**
	 * Infers template types
	 *
	 * Infers the real Type of the TemplateTypes found in $this, based on
	 * the received Type.
	 */
	public function inferTemplateTypes(Type $receivedType): TemplateTypeMap;

	/**
	 * Traverses inner types
	 *
	 * Returns a new instance with all inner types mapped through $cb. Might
	 * return the same instance if inner types did not change.
	 *
	 * @param callable(Type):Type $cb
	 */
	public function traverse(callable $cb): Type;

	/**
	 * @param mixed[] $properties
	 * @return self
	 */
	public static function __set_state(array $properties): self;

}
