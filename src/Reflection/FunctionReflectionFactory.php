<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\Reflection\Php\PhpFunctionReflection;
use PHPStan\Type\Type;

interface FunctionReflectionFactory
{

	/**
	 * @param \ReflectionFunction $reflection
	 * @param \PHPStan\Type\Type[] $phpDocParameterTypes
	 * @param Type|null $phpDocReturnType
	 * @param Type|null $phpDocThrowType
	 * @param bool $isDeprecated
	 * @param bool $isInternal
	 * @param bool $isFinal
	 * @param string|false $filename
	 * @return PhpFunctionReflection
	 */
	public function create(
		\ReflectionFunction $reflection,
		array $phpDocParameterTypes,
		?Type $phpDocReturnType,
		?Type $phpDocThrowType,
		bool $isDeprecated,
		bool $isInternal,
		bool $isFinal,
		$filename
	): PhpFunctionReflection;

}
