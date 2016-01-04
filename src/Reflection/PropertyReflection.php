<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

interface PropertyReflection
{

	public function getDeclaringClass(): ClassReflection;

	public function isStatic(): bool;

	public function isPrivate(): bool;

	public function isPublic(): bool;

}
