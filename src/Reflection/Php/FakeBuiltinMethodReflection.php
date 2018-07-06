<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

class FakeBuiltinMethodReflection implements BuiltinMethodReflection
{

	/** @var string */
	private $methodName;

	/** @var \ReflectionClass */
	private $declaringClass;

	public function __construct(
		string $methodName,
		\ReflectionClass $declaringClass
	)
	{
		$this->methodName = $methodName;
		$this->declaringClass = $declaringClass;
	}

	public function getName(): string
	{
		return $this->methodName;
	}

	/**
	 * @return string|false
	 */
	public function getFileName()
	{
		return false;
	}

	public function getDeclaringClass(): \ReflectionClass
	{
		return $this->declaringClass;
	}

	/**
	 * @return int|false
	 */
	public function getStartLine()
	{
		return false;
	}

	/**
	 * @return string|false
	 */
	public function getDocComment()
	{
		return false;
	}

	public function isStatic(): bool
	{
		return false;
	}

	public function isPrivate(): bool
	{
		return false;
	}

	public function isPublic(): bool
	{
		return true;
	}

	public function getPrototype(): BuiltinMethodReflection
	{
		throw new \ReflectionException();
	}

	public function isDeprecated(): bool
	{
		return false;
	}

	public function isVariadic(): bool
	{
		return false;
	}

	public function getReturnType(): ?\ReflectionType
	{
		return null;
	}

	/**
	 * @return \ReflectionParameter[]
	 */
	public function getParameters(): array
	{
		return [];
	}

}
