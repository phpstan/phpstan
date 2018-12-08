<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\Type\Type;

interface ThrowableReflection
{

	public function getThrowType(): ?Type;

}
