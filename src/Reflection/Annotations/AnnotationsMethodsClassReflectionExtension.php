<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Annotations;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Type\FileTypeMapper;

class AnnotationsMethodsClassReflectionExtension implements MethodsClassReflectionExtension
{

	/** @var FileTypeMapper */
	private $fileTypeMapper;

	/** @var MethodReflection[][] */
	private $methods = [];

	public function __construct(FileTypeMapper $fileTypeMapper)
	{
		$this->fileTypeMapper = $fileTypeMapper;
	}

	public function hasMethod(ClassReflection $classReflection, string $methodName): bool
	{
		if (!isset($this->methods[$classReflection->getName()])) {
			$this->methods[$classReflection->getName()] = $this->createMethods($classReflection, $classReflection);
		}

		return isset($this->methods[$classReflection->getName()][$methodName]);
	}

	public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
	{
		return $this->methods[$classReflection->getName()][$methodName];
	}

	/**
	 * @param ClassReflection $classReflection
	 * @param ClassReflection $declaringClass
	 * @return MethodReflection[]
	 */
	private function createMethods(
		ClassReflection $classReflection,
		ClassReflection $declaringClass
	): array
	{
		$methods = [];
		foreach ($classReflection->getTraits() as $traitClass) {
			$methods += $this->createMethods($traitClass, $classReflection);
		}
		foreach ($classReflection->getParents() as $parentClass) {
			$methods += $this->createMethods($parentClass, $parentClass);
			foreach ($parentClass->getTraits() as $traitClass) {
				$methods += $this->createMethods($traitClass, $parentClass);
			}
		}
		foreach ($classReflection->getInterfaces() as $interfaceClass) {
			$methods += $this->createMethods($interfaceClass, $interfaceClass);
		}

		$fileName = $classReflection->getFileName();
		if ($fileName === false) {
			return $methods;
		}

		$docComment = $classReflection->getNativeReflection()->getDocComment();
		if ($docComment === false) {
			return $methods;
		}

		$resolvedPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc($fileName, $classReflection->getName(), $docComment);
		foreach ($resolvedPhpDoc->getMethodTags() as $methodName => $methodTag) {
			$parameters = [];
			foreach ($methodTag->getParameters() as $parameterName => $parameterTag) {
				$parameters[] = new AnnotationsMethodParameterReflection(
					$parameterName,
					$parameterTag->getType(),
					$parameterTag->isPassedByReference(),
					$parameterTag->isOptional(),
					$parameterTag->isVariadic()
				);
			}

			$methods[$methodName] = new AnnotationMethodReflection(
				$methodName,
				$declaringClass,
				$methodTag->getReturnType(),
				$parameters,
				$methodTag->isStatic(),
				$this->detectMethodVariadic($parameters)
			);
		}
		return $methods;
	}

	/**
	 * @param AnnotationsMethodParameterReflection[] $parameters
	 * @return bool
	 */
	private function detectMethodVariadic(array $parameters): bool
	{
		if ($parameters === []) {
			return false;
		}

		$possibleVariadicParameterIndex = count($parameters) - 1;
		$possibleVariadicParameter = $parameters[$possibleVariadicParameterIndex];

		return $possibleVariadicParameter->isVariadic();
	}

}
