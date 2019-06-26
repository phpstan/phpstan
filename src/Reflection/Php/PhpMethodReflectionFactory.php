<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\Type;

interface PhpMethodReflectionFactory
{

	/**
	 * @param \PHPStan\Reflection\ClassReflection $declaringClass
	 * @param \PHPStan\Reflection\ClassReflection|null $declaringTrait
	 * @param BuiltinMethodReflection $reflection
	 * @param TemplateTypeMap $templateTypeMap
	 * @param \PHPStan\Type\Type[] $phpDocParameterTypes
	 * @param \PHPStan\Type\Type|null $phpDocReturnType
	 * @param \PHPStan\Type\Type|null $phpDocThrowType
	 * @param string|null $deprecatedDescription
	 * @param bool $isDeprecated
	 * @param bool $isInternal
	 * @param bool $isFinal
	 *
	 * @return \PHPStan\Reflection\Php\PhpMethodReflection
	 */
	public function create(
		ClassReflection $declaringClass,
		?ClassReflection $declaringTrait,
		BuiltinMethodReflection $reflection,
		TemplateTypeMap $templateTypeMap,
		array $phpDocParameterTypes,
		?Type $phpDocReturnType,
		?Type $phpDocThrowType,
		?string $deprecatedDescription,
		bool $isDeprecated,
		bool $isInternal,
		bool $isFinal
	): PhpMethodReflection;

}
