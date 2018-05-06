<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

class ClassConstantReflection implements ConstantReflection, DeprecatableReflection
{

	/** @var \PHPStan\Reflection\ClassReflection */
	private $declaringClass;

	/** @var \ReflectionClassConstant */
	private $reflection;

	/** @var bool */
	private $isDeprecated;

	public function __construct(
		ClassReflection $declaringClass,
		\ReflectionClassConstant $reflection,
		bool $isDeprecated
	)
	{
		$this->declaringClass = $declaringClass;
		$this->reflection = $reflection;
		$this->isDeprecated = $isDeprecated;
	}

	public function getName(): string
	{
		return $this->reflection->getName();
	}

	/**
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->reflection->getValue();
	}

	public function getDeclaringClass(): ClassReflection
	{
		return $this->declaringClass;
	}

	public function isStatic(): bool
	{
		return true;
	}

	public function isPrivate(): bool
	{
		return $this->reflection->isPrivate();
	}

	public function isPublic(): bool
	{
		return $this->reflection->isPublic();
	}

	public function isDeprecated(): bool
	{
		return $this->isDeprecated;
	}

}
