<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

class ObsoleteClassConstantReflection implements ClassConstantReflection
{

	/** @var \PHPStan\Reflection\ClassReflection */
	private $declaringClass;

	/** @var string */
	private $name;

	/** @var mixed */
	private $value;

	/**
	 * @param \PHPStan\Reflection\ClassReflection $declaringClass
	 * @param string $name
	 * @param mixed $value
	 */
	public function __construct(
		ClassReflection $declaringClass,
		string $name,
		$value
	)
	{
		$this->declaringClass = $declaringClass;
		$this->name = $name;
		$this->value = $value;
	}

	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
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
		return false;
	}

	public function isPublic(): bool
	{
		return true;
	}

}
