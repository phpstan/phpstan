<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

interface ClassMemberReflection
{

	public function getDeclaringClass(): ClassReflection;

	public function isStatic(): bool;

	public function isPrivate(): bool;

	public function isPublic(): bool;

}
