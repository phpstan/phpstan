<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\TrinaryLogic;

interface DeprecatableReflection
{

	public function isDeprecated(): TrinaryLogic;

	public function getDeprecatedDescription(): ?string;

}
