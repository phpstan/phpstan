<?php declare(strict_types = 1);

namespace PHPStan\Type;

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

	public function combineWith(Type $otherType): Type;

	public function accepts(Type $type): bool;

	public function describe(): string;

	public function canAccessProperties(): bool;

	public function canCallMethods(): bool;

	public function isDocumentableNatively(): bool;

	public function isIterable(): TrinaryLogic;

	public function getIterableKeyType(): Type;

	public function getIterableValueType(): Type;

	public static function __set_state(array $properties): self;

}
