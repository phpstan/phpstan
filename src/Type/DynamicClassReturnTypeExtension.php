<?php declare(strict_types = 1);

namespace PHPStan\Type;

interface DynamicClassReturnTypeExtension
{

	public function isClassSupported(string $class): bool;

}
