<?php declare(strict_types = 1);

namespace PHPStan\TypeX;

use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Type\Type;


interface TypeX extends Type
{
	const RESULT_YES = 0;
	const RESULT_MAYBE = 1;
	const RESULT_NO = 2;

	public function describe(): string;

	/**
	 * Returns true iff $otherType is guaranteed to be subset of $this.
	 */
	public function acceptsX(TypeX $otherType): bool;

//	/**
//	 * Returns true iff $otherType is NOT guaranteed to NOT be subset of $this
//	 */
//	public function maybeAccept(TypeX $otherType): bool;

// --- AssignableTypeInterface -------------------------------------------------
	public function isAssignable(): int;

// --- IterableTypeInterface ---------------------------------------------------
	public function isIterable(): int;

	public function getIterableKeyType(): TypeX;

	public function getIterableValueType(): TypeX;

// --- CallableTypeInterface ---------------------------------------------------
	public function isCallable(): int;

	// public function getCallTargets(TypeX ...$callArgsTypes): array;

// --- HasMethodsTypeInterface -------------------------------------------------
	public function canCallMethodsX(): int;

	// public function canCallMethod(string $methodName): int;

	// public function getMethodCallReturnType(TypeX ...$callArgsTypes): TypeX;

// --- HasPropertiesTypeInterface ----------------------------------------------
	public function canAccessPropertiesX(): int; // TODO: unify with canCallMethodsX to canAccessMembers?

	// public function canAccessProperty(string $propertyName): string;

	// public function getPropertyType(): TypeX;

// --- HasArrayAccessTypeInterface ---------------------------------------------
	public function canAccessOffset(): int;

	public function getOffsetValueType(TypeX $offsetType): TypeX;

	public function setOffsetValueType(TypeX $offsetType = null, TypeX $valueType): TypeX;

// --- utils for rules ---------------------------------------------------------
//	public function getInnerTypes(): iterable;

// --- StaticResolvableTypeInterface -------------------------------------------
//	public function resolveStatic(string $className): TypeX;

// --- To be destroyed in the future -------------------------------------------
//	public function canBeDocumentedNatively(): bool;

//	/**
//	 * @return string|null
//	 */
//	public function getClass();
//
//	/**
//	 * @return string[]
//	 */
//	public function getReferencedClasses(): array;
//
//	public function combineWith(TypeX $otherType): TypeX;

}
