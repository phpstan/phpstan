<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

interface DeprecatableReflection
{

	public function isDeprecated(): bool;

	public function getDeprecatedDescription(): ?string;

}
