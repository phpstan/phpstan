<?php declare(strict_types = 1);

namespace PHPStan\Reflection\PhpParser;

use PhpParser\Node\Name;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

class NamespacedNameProperty implements \PHPStan\Reflection\PropertyReflection
{

	/** @var \PHPStan\Reflection\ClassReflection */
	private $declaringClass;

	public function __construct(ClassReflection $declaringClass)
	{
		$this->declaringClass = $declaringClass;
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
		return new ObjectType(Name::class, false);
	}

}
