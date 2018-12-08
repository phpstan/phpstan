<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

class NativeBuiltinMethodReflection implements BuiltinMethodReflection
{

	/** @var \ReflectionMethod */
	private $reflection;

	public function __construct(\ReflectionMethod $reflection)
	{
		$this->reflection = $reflection;
	}

	public function getName(): string
	{
		return $this->reflection->getName();
	}

	/**
	 * @return string|false
	 */
	public function getFileName()
	{
		return $this->reflection->getFileName();
	}

	public function getDeclaringClass(): \ReflectionClass
	{
		return $this->reflection->getDeclaringClass();
	}

	/**
	 * @return int|false
	 */
	public function getStartLine()
	{
		return $this->reflection->getStartLine();
	}

	/**
	 * @return string|false
	 */
	public function getDocComment()
	{
		return $this->reflection->getDocComment();
	}

	public function isStatic(): bool
	{
		return $this->reflection->isStatic();
	}

	public function isPrivate(): bool
	{
		return $this->reflection->isPrivate();
	}

	public function isPublic(): bool
	{
		return $this->reflection->isPublic();
	}

	public function getPrototype(): BuiltinMethodReflection
	{
		return new self($this->reflection->getPrototype());
	}

	public function isDeprecated(): bool
	{
		return $this->reflection->isDeprecated();
	}

	public function isVariadic(): bool
	{
		return $this->reflection->isVariadic();
	}

	public function getReturnType(): ?\ReflectionType
	{
		return $this->reflection->getReturnType();
	}

	/**
	 * @return \ReflectionParameter[]
	 */
	public function getParameters(): array
	{
		return $this->reflection->getParameters();
	}

}
