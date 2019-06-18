<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ExtendedPropertyReflection;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

class SimpleXMLElementProperty implements ExtendedPropertyReflection
{

	/** @var \PHPStan\Reflection\ClassReflection */
	private $declaringClass;

	/** @var \PHPStan\Type\Type */
	private $type;

	public function __construct(
		ClassReflection $declaringClass,
		Type $type
	)
	{
		$this->declaringClass = $declaringClass;
		$this->type = $type;
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

	public function getWritableType(): Type
	{
		return TypeCombinator::union(
			$this->type,
			new IntegerType(),
			new FloatType(),
			new StringType(),
			new BooleanType()
		);
	}

	public function isReadable(): bool
	{
		return true;
	}

	public function isWritable(): bool
	{
		return true;
	}

	public function canChangeTypeAfterAssignment(): bool
	{
		return false;
	}

}
