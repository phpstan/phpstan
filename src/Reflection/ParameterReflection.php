<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\Type\Type;

interface ParameterReflection
{

	public function getName(): string;

	public function isOptional(): bool;

	public function getType(): Type;

	public function isPassedByReference(): bool;

	public function isVariadic(): bool;

}
