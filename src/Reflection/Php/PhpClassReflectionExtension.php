<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

use PHPStan\Broker\Broker;
use PHPStan\Reflection\BrokerAwareClassReflectionExtension;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\MixedType;

class PhpClassReflectionExtension
	implements PropertiesClassReflectionExtension, MethodsClassReflectionExtension, BrokerAwareClassReflectionExtension
{

	/** @var \PHPStan\Reflection\Php\PhpMethodReflectionFactory */
	private $methodReflectionFactory;

	/** @var \PHPStan\Type\FileTypeMapper */
	private $fileTypeMapper;

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	private $properties = [];

	private $methods = [];

	public function __construct(
		PhpMethodReflectionFactory $methodReflectionFactory,
		FileTypeMapper $fileTypeMapper
	)
	{
		$this->methodReflectionFactory = $methodReflectionFactory;
		$this->fileTypeMapper = $fileTypeMapper;
	}

	public function setBroker(Broker $broker)
	{
		$this->broker = $broker;
	}

	public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
	{
		return $classReflection->getNativeReflection()->hasProperty($propertyName);
	}

	public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
	{
		if (!isset($this->properties[$classReflection->getName()])) {
			$this->properties[$classReflection->getName()] = $this->createProperties($classReflection);
		}

		return $this->properties[$classReflection->getName()][$propertyName];
	}

	/**
	 * @param \PHPStan\Reflection\ClassReflection $classReflection
	 * @return \PHPStan\Reflection\PropertyReflection[]
	 */
	private function createProperties(ClassReflection $classReflection): array
	{
		$properties = [];
		foreach ($classReflection->getNativeReflection()->getProperties() as $propertyReflection) {
			$propertyName = $propertyReflection->getName();
			$declaringClassReflection = $this->broker->getClass($propertyReflection->getDeclaringClass()->getName());
			$typeString = $this->getPropertyAnnotationTypeString($propertyReflection);
			if ($typeString === null) {
				$type = new MixedType(false);
			} elseif (!$declaringClassReflection->getNativeReflection()->isAnonymous() && $declaringClassReflection->getNativeReflection()->getFileName() !== false) {
				$typeMap = $this->fileTypeMapper->getTypeMap($declaringClassReflection->getNativeReflection()->getFileName());
				if (isset($typeMap[$typeString])) {
					$type = $typeMap[$typeString];
				} else {
					$type = new MixedType(true);
				}
			} else {
				$type = new MixedType(true);
			}

			$properties[$propertyName] = new PhpPropertyReflection(
				$declaringClassReflection,
				$type,
				$propertyReflection
			);
		}

		return $properties;
	}

	/**
	 * @param \ReflectionMethod $reflectionMethod
	 * @return mixed[]
	 */
	private function getPhpDocParamsFromMethod(\ReflectionMethod $reflectionMethod): array
	{
		$phpDoc = $reflectionMethod->getDocComment();
		if ($phpDoc === false) {
			return [];
		}

		preg_match_all('#@param\s+' . FileTypeMapper::TYPE_PATTERN . '\s+\$([a-zA-Z0-9_]+)#', $phpDoc, $matches, PREG_SET_ORDER);
		$phpDocParams = [];
		foreach ($matches as $match) {
			$typeString = $match[1];
			$parameterName = $match[2];
			if (!isset($phpDocParams[$parameterName])) {
				$phpDocParams[$parameterName] = [];
			}

			$phpDocParams[$parameterName][] = $typeString;
		}

		return $phpDocParams;
	}

	/**
	 * @param \ReflectionMethod $reflectionMethod
	 * @return string|null
	 */
	private function getReturnTypeStringFromMethod(\ReflectionMethod $reflectionMethod)
	{
		$phpDoc = $reflectionMethod->getDocComment();
		if ($phpDoc === false) {
			return null;
		}

		$count = preg_match_all('#@return\s+' . FileTypeMapper::TYPE_PATTERN . '#', $phpDoc, $matches);
		if ($count !== 1) {
			return null;
		}

		return $matches[1][0];
	}

	/**
	 * @param mixed[] $phpDocParams
	 * @param \ReflectionParameter $parameterReflection
	 * @return string|null
	 */
	private function getMethodParameterAnnotationTypeString(array $phpDocParams, \ReflectionParameter $parameterReflection)
	{
		if (!isset($phpDocParams[$parameterReflection->getName()])) {
			return null;
		}

		$typeStrings = $phpDocParams[$parameterReflection->getName()];
		if (count($typeStrings) > 1) {
			return null;
		}

		return $typeStrings[0];
	}

	/**
	 * @param \ReflectionProperty $propertyReflection
	 * @return string|null
	 */
	private function getPropertyAnnotationTypeString(\ReflectionProperty $propertyReflection)
	{
		$phpDoc = $propertyReflection->getDocComment();
		if ($phpDoc === false) {
			return null;
		}

		$count = preg_match_all('#@var\s+' . FileTypeMapper::TYPE_PATTERN . '#', $phpDoc, $matches);
		if ($count !== 1) {
			return null;
		}

		return $matches[1][0];
	}

	public function hasMethod(ClassReflection $classReflection, string $methodName): bool
	{
		return $classReflection->getNativeReflection()->hasMethod($methodName);
	}

	public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
	{
		if (!isset($this->methods[$classReflection->getName()])) {
			$this->methods[$classReflection->getName()] = $this->createMethods($classReflection);
		}

		$methodName = strtolower($methodName);

		return $this->methods[$classReflection->getName()][$methodName];
	}

	/**
	 * @param \PHPStan\Reflection\ClassReflection $classReflection
	 * @return \PHPStan\Reflection\MethodReflection[]
	 */
	private function createMethods(ClassReflection $classReflection): array
	{
		$methods = [];
		foreach ($classReflection->getNativeReflection()->getMethods() as $methodReflection) {
			$declaringClass = $this->broker->getClass($methodReflection->getDeclaringClass()->getName());
			$phpDocParameters = $this->getPhpDocParamsFromMethod($methodReflection);
			$phpDocParameterTypes = [];

			if (!$declaringClass->getNativeReflection()->isAnonymous() && $declaringClass->getNativeReflection()->getFileName() !== false) {
				$typeMap = $this->fileTypeMapper->getTypeMap($declaringClass->getNativeReflection()->getFileName());
				foreach ($methodReflection->getParameters() as $parameterReflection) {
					$typeString = $this->getMethodParameterAnnotationTypeString($phpDocParameters, $parameterReflection);
					if ($typeString === null || !isset($typeMap[$typeString])) {
						continue;
					}

					$type = $typeMap[$typeString];

					$phpDocParameterTypes[$parameterReflection->getName()] = $type;
				}
			}

			$phpDocReturnType = null;
			$returnTypeString = $this->getReturnTypeStringFromMethod($methodReflection);
			if ($returnTypeString !== null && isset($typeMap[$returnTypeString])) {
				$phpDocReturnType = $typeMap[$returnTypeString];
			}

			$methods[strtolower($methodReflection->getName())] = $this->methodReflectionFactory->create(
				$declaringClass,
				$methodReflection,
				$phpDocParameterTypes,
				$phpDocReturnType
			);
		}

		return $methods;
	}

}
