<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\Type\Type;

interface ExtendedPropertyReflection extends PropertyReflection
{

	public function getWritableType(): Type;

	public function canChangeTypeAfterAssignment(): bool;

}
