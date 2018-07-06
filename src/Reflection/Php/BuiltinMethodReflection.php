<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

interface BuiltinMethodReflection
{

	public function getName(): string;

	/**
	 * @return string|false
	 */
	public function getFileName();

	public function getDeclaringClass(): \ReflectionClass;

	/**
	 * @return int|false
	 */
	public function getStartLine();

	/**
	 * @return string|false
	 */
	public function getDocComment();

	public function isStatic(): bool;

	public function isPrivate(): bool;

	public function isPublic(): bool;

	public function getPrototype(): self;

	public function isDeprecated(): bool;

	public function isVariadic(): bool;

	public function getReturnType(): ?\ReflectionType;

	/**
	 * @return \ReflectionParameter[]
	 */
	public function getParameters(): array;

}
