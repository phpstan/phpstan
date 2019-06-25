<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Dummy;

use PHPStan\Reflection\ClassMemberReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionVariant;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\VoidType;

class DummyConstructorReflection implements MethodReflection
{

	/** @var ClassReflection */
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

	public function getName(): string
	{
		return '__construct';
	}

	public function getPrototype(): ClassMemberReflection
	{
		return $this;
	}

	public function getVariants(): array
	{
		return [
			new FunctionVariant(
				[],
				false,
				new VoidType()
			),
		];
	}

}
