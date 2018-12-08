<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\Type\Type;

interface PropertyReflection extends ClassMemberReflection
{

	public function getType(): Type;

	public function isReadable(): bool;

	public function isWritable(): bool;

}
