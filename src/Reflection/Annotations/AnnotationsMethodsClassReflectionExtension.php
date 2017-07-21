<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Annotations;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\TypeCombinator;

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

		$fileName = $classReflection->getNativeReflection()->getFileName();
		if ($fileName === false) {
			return $methods;
		}

		$docComment = $classReflection->getNativeReflection()->getDocComment();
		if ($docComment === false) {
			return $methods;
		}

		$typeMap = $this->fileTypeMapper->getTypeMap($fileName);

		preg_match_all('#@method\s+(?:(?P<IsStatic>static)\s+)?(?:(?P<Type>[^\(\*]+?)(?<!\|)\s+)?(?P<MethodName>[a-zA-Z0-9_]+)(?P<Parameters>(?:\([^\)]*\))?)#', $docComment, $matches, PREG_SET_ORDER);
		foreach ($matches as $match) {
			$isStatic = $match['IsStatic'] === 'static';
			$typeStringCandidate = $match['Type'];
			if (preg_match('#(?P<Type>' . FileTypeMapper::TYPE_PATTERN . ')#', $typeStringCandidate, $typeStringMatches)) {
				$typeString = $typeStringMatches['Type'];
				if (!isset($typeMap[$typeString])) {
					continue;
				}
				$returnType = $typeMap[$typeString];
			} else {
				$returnType = new MixedType();
			}
			$methodName = $match['MethodName'];
			$parametersStringCandidate = trim(trim($match['Parameters'], '()'));
			$parameters = $this->createMethodParameters($parametersStringCandidate, $typeMap);
			$isVariadic = $this->detectMethodVariadic($parameters);
			$methods[$methodName] = new AnnotationMethodReflection($methodName, $declaringClass, $returnType, $parameters, $isStatic, $isVariadic);
		}
		return $methods;
	}

	/**
	 * @param string $parametersStringCandidate
	 * @param \PHPStan\Type\Type[] $typeMap
	 * @return AnnotationsMethodParameterReflection[]
	 */
	private function createMethodParameters(string $parametersStringCandidate, array $typeMap): array
	{
		$parameters = [];
		if (!$parametersStringCandidate) {
			return $parameters;
		}

		foreach (preg_split('#\s*,\s*#', $parametersStringCandidate) as $parameter) {
			if (preg_match('#(?:(?P<Type>' . FileTypeMapper::TYPE_PATTERN . ')\s+)?(?P<IsVariadic>...)?(?P<IsPassedByReference>\&)?\$(?P<Name>[a-zA-Z0-9_]+)(?:\s*=\s*(?P<DefaultValue>.+))?#', $parameter, $parameterMatches)) {
				$name = $parameterMatches['Name'];
				$typeString = $parameterMatches['Type'];
				$defaultValue = $parameterMatches['DefaultValue'] ?? null;
				if ($typeString !== '') {
					if (!isset($typeMap[$typeString])) {
						continue;
					}
					$type = $typeMap[$typeString];
					if ($defaultValue === 'null') {
						$type = $type !== null ? TypeCombinator::addNull($type) : new NullType();
					}
				} else {
					$type = new MixedType();
				}
				$isVariadic = !empty($parameterMatches['IsVariadic']);
				$isPassedByReference = !empty($parameterMatches['IsPassedByReference']);
				$isOptional = !empty($defaultValue);

				$parameters[] = new AnnotationsMethodParameterReflection($name, $type, $isPassedByReference, $isOptional, $isVariadic);
			}
		}

		return $parameters;
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
