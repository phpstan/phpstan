<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\TrinaryLogic;

interface FinalizableReflection
{

	public function isFinal(): TrinaryLogic;

}
