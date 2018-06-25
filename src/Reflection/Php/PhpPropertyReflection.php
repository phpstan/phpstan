<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\DeprecatableReflection;
use PHPStan\Reflection\InternableReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Type\Type;

class PhpPropertyReflection implements PropertyReflection, DeprecatableReflection, InternableReflection
{

	/** @var \PHPStan\Reflection\ClassReflection */
	private $declaringClass;

	/** @var \PHPStan\Type\Type */
	private $type;

	/** @var \ReflectionProperty */
	private $reflection;

	/** @var bool */
	private $isDeprecated;

	/** @var bool */
	private $isInternal;

	public function __construct(
		ClassReflection $declaringClass,
		Type $type,
		\ReflectionProperty $reflection,
		bool $isDeprecated,
		bool $isInternal
	)
	{
		$this->declaringClass = $declaringClass;
		$this->type = $type;
		$this->reflection = $reflection;
		$this->isDeprecated = $isDeprecated;
		$this->isInternal = $isInternal;
	}

	public function getDeclaringClass(): ClassReflection
	{
		return $this->declaringClass;
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

	public function getType(): Type
	{
		return $this->type;
	}

	public function isReadable(): bool
	{
		return true;
	}

	public function isWritable(): bool
	{
		return true;
	}

	public function isDeprecated(): bool
	{
		return $this->isDeprecated;
	}

	public function isInternal(): bool
	{
		return $this->isInternal;
	}

}
