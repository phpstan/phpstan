<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\PropertyReflection;

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

	public function combineWith(Type $otherType): Type;

	public function accepts(Type $type): bool;

	public function describe(): string;

	public function canAccessProperties(): bool;

	public function canCallMethods(): bool;

	public function isDocumentableNatively(): bool;

	public function isIterable(): int;

	public function getIterableKeyType(): Type;

	public function getIterableValueType(): Type;

	/**
	 * @param string $name
	 * @param string|null $scope
	 * @return int
	 */
	// public function hasProperty(string $name, $scope): int;

	/**
	 * @param string $name
	 * @param string|null $scope
	 * @return PropertyReflection
	 */
	// public function getProperty(string $name, $scope): PropertyReflection;

	public static function __set_state(array $properties): self;

}
