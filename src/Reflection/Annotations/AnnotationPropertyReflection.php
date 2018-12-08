<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Annotations;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Type\Type;

class AnnotationPropertyReflection implements PropertyReflection
{

	/** @var \PHPStan\Reflection\ClassReflection */
	private $declaringClass;

	/** @var \PHPStan\Type\Type */
	private $type;

	/** @var bool */
	private $readable;

	/** @var bool */
	private $writable;

	public function __construct(
		ClassReflection $declaringClass,
		Type $type,
		bool $readable = true,
		bool $writable = true
	)
	{
		$this->declaringClass = $declaringClass;
		$this->type = $type;
		$this->readable = $readable;
		$this->writable = $writable;
	}

	public function getDeclaringClass(): ClassReflection
	{
		return $this->declaringClass;
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

	public function getType(): Type
	{
		return $this->type;
	}

	public function isReadable(): bool
	{
		return $this->readable;
	}

	public function isWritable(): bool
	{
		return $this->writable;
	}

}
