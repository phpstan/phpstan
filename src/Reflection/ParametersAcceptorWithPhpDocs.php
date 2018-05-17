<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\Type\Type;

interface ParametersAcceptorWithPhpDocs extends ParametersAcceptor
{

	/**
	 * @return array<int, \PHPStan\Reflection\Php\PhpParameterReflection>
	 */
	public function getParameters(): array;

	public function getPhpDocReturnType(): Type;

	public function getNativeReturnType(): Type;

}
