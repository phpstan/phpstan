<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Dummy;

use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;

class DummyPropertyReflection implements PropertyReflection
{

	public function getDeclaringClass(): ClassReflection
	{
		$broker = Broker::getInstance();

		return $broker->getClass(\stdClass::class);
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
		return new MixedType();
	}

	public function isReadable(): bool
	{
		return true;
	}

	public function isWritable(): bool
	{
		return true;
	}

}
