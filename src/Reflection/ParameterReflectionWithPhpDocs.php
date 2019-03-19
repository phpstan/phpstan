<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\Type\Type;

interface ParameterReflectionWithPhpDocs extends ParameterReflection
{

	public function getPhpDocType(): Type;

	public function getNativeType(): Type;

}
