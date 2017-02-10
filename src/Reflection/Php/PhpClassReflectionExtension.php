<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

use PHPStan\Broker\Broker;
use PHPStan\PhpDoc\PhpDocBlock;
use PHPStan\Reflection\BrokerAwareClassReflectionExtension;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\MixedType;
use PHPStan\Type\TypehintHelper;

class PhpClassReflectionExtension
	implements PropertiesClassReflectionExtension, MethodsClassReflectionExtension, BrokerAwareClassReflectionExtension
{

	/** @var \PHPStan\Reflection\Php\PhpMethodReflectionFactory */
	private $methodReflectionFactory;

	/** @var \PHPStan\Type\FileTypeMapper */
	private $fileTypeMapper;

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	/** @var \PHPStan\Reflection\PropertyReflection[][] */
	private $properties = [];

	/** @var \PHPStan\Reflection\MethodReflection[][] */
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
			if ($propertyReflection->getDocComment() === false) {
				$type = new MixedType();
			} elseif (!$declaringClassReflection->getNativeReflection()->isAnonymous() && $declaringClassReflection->getNativeReflection()->getFileName() !== false) {
				$phpDocBlock = PhpDocBlock::resolvePhpDocBlockForProperty(
					$this->broker,
					$propertyReflection->getDocComment(),
					$declaringClassReflection->getName(),
					$propertyName,
					$declaringClassReflection->getNativeReflection()->getFileName()
				);
				$typeMap = $this->fileTypeMapper->getTypeMap($phpDocBlock->getFile());
				$typeString = $this->getPropertyAnnotationTypeString($phpDocBlock->getDocComment());
				if (isset($typeMap[$typeString])) {
					$type = $typeMap[$typeString];
				} else {
					$type = new MixedType();
				}
			} else {
				$type = new MixedType();
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
	 * @param string $phpDoc
	 * @return string|null
	 */
	private function getPropertyAnnotationTypeString(string $phpDoc)
	{
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

		$nativeMethodReflection = $classReflection->getNativeReflection()->getMethod($methodName);

		return $this->methods[$classReflection->getName()][$nativeMethodReflection->getName()];
	}

	/**
	 * @param \PHPStan\Reflection\ClassReflection $classReflection
	 * @return \PHPStan\Reflection\MethodReflection[]
	 */
	private function createMethods(ClassReflection $classReflection): array
	{
		$methods = [];
		$reflectionMethods = $classReflection->getNativeReflection()->getMethods();
		if ($classReflection->getName() === \Closure::class || $classReflection->isSubclassOf(\Closure::class)) {
			$hasInvokeMethod = false;
			foreach ($reflectionMethods as $reflectionMethod) {
				if ($reflectionMethod->getName() === '__invoke') {
					$hasInvokeMethod = true;
					break;
				}
			}
			if (!$hasInvokeMethod) {
				$reflectionMethods[] = $classReflection->getNativeReflection()->getMethod('__invoke');
			}
		}
		foreach ($reflectionMethods as $methodReflection) {
			$declaringClass = $this->broker->getClass($methodReflection->getDeclaringClass()->getName());

			$phpDocParameterTypes = [];
			$phpDocReturnType = null;
			if (!$declaringClass->getNativeReflection()->isAnonymous() && $declaringClass->getNativeReflection()->getFileName() !== false) {
				if ($methodReflection->getDocComment() !== false) {
					$phpDocBlock = PhpDocBlock::resolvePhpDocBlockForMethod(
						$this->broker,
						$methodReflection->getDocComment(),
						$declaringClass->getName(),
						$methodReflection->getName(),
						$declaringClass->getNativeReflection()->getFileName()
					);
					$typeMap = $this->fileTypeMapper->getTypeMap($phpDocBlock->getFile());
					$phpDocParameterTypes = TypehintHelper::getParameterTypesFromPhpDoc(
						$typeMap,
						array_map(function (\ReflectionParameter $parameterReflection): string {
							return $parameterReflection->getName();
						}, $methodReflection->getParameters()),
						$phpDocBlock->getDocComment()
					);
					$phpDocReturnType = TypehintHelper::getReturnTypeFromPhpDoc($typeMap, $phpDocBlock->getDocComment());
				}
			}

			$methods[$methodReflection->getName()] = $this->methodReflectionFactory->create(
				$declaringClass,
				$methodReflection,
				$phpDocParameterTypes,
				$phpDocReturnType
			);
		}

		return $methods;
	}

}
