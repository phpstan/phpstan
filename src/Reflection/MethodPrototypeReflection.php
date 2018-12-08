<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

class MethodPrototypeReflection implements ClassMemberReflection
{

	/** @var \PHPStan\Reflection\ClassReflection */
	private $declaringClass;

	/** @var bool */
	private $isStatic;

	/** @var bool */
	private $isPrivate;

	/** @var bool */
	private $isPublic;

	public function __construct(
		ClassReflection $declaringClass,
		bool $isStatic,
		bool $isPrivate,
		bool $isPublic
	)
	{
		$this->declaringClass = $declaringClass;
		$this->isStatic = $isStatic;
		$this->isPrivate = $isPrivate;
		$this->isPublic = $isPublic;
	}

	public function getDeclaringClass(): ClassReflection
	{
		return $this->declaringClass;
	}

	public function isStatic(): bool
	{
		return $this->isStatic;
	}

	public function isPrivate(): bool
	{
		return $this->isPrivate;
	}

	public function isPublic(): bool
	{
		return $this->isPublic;
	}

}
