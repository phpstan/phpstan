<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Native;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Type;

class NativeMethodReflection implements MethodReflection
{

	/** @var \PHPStan\Reflection\ClassReflection */
	private $declaringClass;

	/** @var \ReflectionMethod */
	private $reflection;

	/** @var bool */
	private $isVariadic;

	/** @var \PHPStan\Reflection\Native\NativeParameterReflection[]  */
	private $parameters;

	/** @var \PHPStan\Type\Type */
	private $returnType;

	/**
	 * @param \PHPStan\Reflection\ClassReflection $declaringClass
	 * @param \ReflectionMethod $reflection
	 * @param bool $isVariadic
	 * @param \PHPStan\Reflection\Native\NativeParameterReflection[] $parameters
	 * @param \PHPStan\Type\Type $returnType
	 */
	public function __construct(
		ClassReflection $declaringClass,
		\ReflectionMethod $reflection,
		bool $isVariadic,
		array $parameters,
		Type $returnType
	)
	{
		$this->declaringClass = $declaringClass;
		$this->reflection = $reflection;
		$this->isVariadic = $isVariadic;
		$this->parameters = $parameters;
		$this->returnType = $returnType;
	}

	public function getDeclaringClass(): ClassReflection
	{
		return $this->declaringClass;
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

	public function getPrototype(): MethodReflection
	{
		return $this;
	}

	public function getName(): string
	{
		return $this->reflection->getName();
	}

	/**
	 * @return \PHPStan\Reflection\Native\NativeParameterReflection[]
	 */
	public function getParameters(): array
	{
		return $this->parameters;
	}

	public function isVariadic(): bool
	{
		return $this->isVariadic;
	}

	public function getReturnType(): Type
	{
		return $this->returnType;
	}

}
