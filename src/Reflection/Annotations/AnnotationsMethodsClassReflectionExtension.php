<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Annotations;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\MixedType;

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
			$this->methods[$classReflection->getName()] = $this->createMethods($classReflection);
		}

		return isset($this->methods[$classReflection->getName()][$methodName]);
	}

	public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
	{
		return $this->methods[$classReflection->getName()][$methodName];
	}

	/**
	 * @param ClassReflection $classReflection
	 * @return MethodReflection[]
	 */
	private function createMethods(ClassReflection $classReflection): array
	{
		$methods = [];
		foreach ($classReflection->getParents() as $parentClass) {
			$methods += $this->createMethods($parentClass);
		}

		$fileName = $classReflection->getNativeReflection()->getFileName();
		if ($fileName === false) {
			return $methods;
		}

		$docComment = $classReflection->getNativeReflection()->getDocComment();
		if ($docComment === false) {
			return $methods;
		}

		$typeMap = $this->fileTypeMapper->getTypeMap($fileName);

		preg_match_all('#@method\s+(?:(.*)\s+)?([a-zA-Z0-9_]+)\(.*\)#', $docComment, $matches, PREG_SET_ORDER);
		foreach ($matches as $match) {
			$typeStringCandidate = $match[1];
			if (preg_match('#' . FileTypeMapper::TYPE_PATTERN . '#', $typeStringCandidate, $typeStringMatches)) {
				$typeString = $typeStringMatches[1];
				if (!isset($typeMap[$typeString])) {
					continue;
				}
				$returnType = $typeMap[$typeString];
			} else {
				$returnType = new MixedType();
			}
			$methodName = $match[2];
			$methods[$methodName] = new AnnotationMethodReflection($methodName, $classReflection, $returnType);
		}
		return $methods;
	}

}
