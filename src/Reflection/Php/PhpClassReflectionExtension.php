<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PHPStan\Broker\Broker;
use PHPStan\Parser\Parser;
use PHPStan\Reflection\BrokerAwareClassReflectionExtension;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypehintHelper;

class PhpClassReflectionExtension
	implements PropertiesClassReflectionExtension, MethodsClassReflectionExtension, BrokerAwareClassReflectionExtension
{

	const CONST_FETCH_CONSTANT = '__PHPSTAN_CLASS_REFLECTION_CONSTANT__';

	/** @var \PHPStan\Reflection\Php\PhpMethodReflectionFactory */
	private $methodReflectionFactory;

	/** @var \PHPStan\Parser\Parser */
	private $parser;

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	private $properties = [];

	private $methods = [];

	private $typeMaps = [];

	public function __construct(
		PhpMethodReflectionFactory $methodReflectionFactory,
		Parser $parser
	)
	{
		$this->methodReflectionFactory = $methodReflectionFactory;
		$this->parser = $parser;
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
			} else {
				$typeMap = $this->getTypeMap($declaringClassReflection);
				if (isset($typeMap[$typeString])) {
					$type = $typeMap[$typeString];
				} else {
					$type = new MixedType(true);
				}
			}

			$properties[$propertyName] = new PhpPropertyReflection(
				$declaringClassReflection,
				$type,
				$propertyReflection
			);
		}

		return $properties;
	}

	private function getTypeMap(ClassReflection $classReflection): array
	{
		if (!isset($this->typeMaps[$classReflection->getName()])) {
			$this->createTypeMap($classReflection);
		}

		return $this->typeMaps[$classReflection->getName()];
	}

	private function createTypeMap(ClassReflection $classReflection)
	{
		$objectTypes = [];
		$typeMap = [];
		$processTypeString = function (string $typeString) use ($classReflection, &$typeMap, &$objectTypes) {
			$type = $this->getTypeFromTypeString($typeString, $classReflection);
			if (isset($typeMap[$typeString])) {
				return;
			}

			if (!($type instanceof ObjectType)) {
				$typeMap[$typeString] = $type;
				return;
			} elseif ($type->getClass() === $classReflection->getName()) {
				$typeMap[$typeString] = $type;
				return;
			}

			$objectTypes[] = [
				'type' => $type,
				'typeString' => $typeString,
			];
		};
		foreach ($classReflection->getNativeReflection()->getProperties() as $propertyReflection) {
			$declaringClass = $propertyReflection->getDeclaringClass();
			if ($declaringClass->getName() !== $classReflection->getName()) {
				$this->getTypeMap($this->broker->getClass($declaringClass->getName()));
				continue;
			}

			$typeString = $this->getPropertyAnnotationTypeString($propertyReflection);
			if ($typeString === null) {
				continue;
			}

			$processTypeString($typeString);
		}

		foreach ($classReflection->getNativeReflection()->getMethods() as $methodReflection) {
			$declaringClass = $methodReflection->getDeclaringClass();
			if ($declaringClass->getName() !== $classReflection->getName()) {
				$this->getTypeMap($this->broker->getClass($declaringClass->getName()));
				continue;
			}

			$phpDocParams = $this->getPhpDocParamsFromMethod($methodReflection);
			foreach ($methodReflection->getParameters() as $parameterReflection) {
				$typeString = $this->getMethodParameterAnnotationTypeString($phpDocParams, $parameterReflection);
				if ($typeString === null) {
					continue;
				}

				$processTypeString($typeString);
			}

			$returnTypeString = $this->getReturnTypeStringFromMethod($methodReflection);
			if ($returnTypeString !== null) {
				$processTypeString($returnTypeString);
			}
		}

		if (count($objectTypes) === 0) {
			$this->typeMaps[$classReflection->getName()] = $typeMap;
			return;
		}

		if (
			$classReflection->getNativeReflection()->getFileName() === false
			|| !file_exists($classReflection->getNativeReflection()->getFileName())
		) {
			$this->typeMaps[$classReflection->getName()] = $typeMap;
			return;
		}

		$classFileString = file_get_contents($classReflection->getNativeReflection()->getFileName());
		$classType = 'class';
		if ($classReflection->isInterface()) {
			$classType = 'interface';
		} elseif ($classReflection->isTrait()) {
			$classType = 'trait';
		}

		$classTypePosition = strpos($classFileString, sprintf('%s %s', $classType, $classReflection->getNativeReflection()->getShortName()));
		if ($classTypePosition === false) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$nameResolveInfluencingPart = trim(substr($classFileString, 0, $classTypePosition));
		if (substr($nameResolveInfluencingPart, -strlen('final')) === 'final') {
			$nameResolveInfluencingPart = trim(substr($nameResolveInfluencingPart, 0, -strlen('final')));
		}

		if (substr($nameResolveInfluencingPart, -strlen('abstract')) === 'abstract') {
			$nameResolveInfluencingPart = trim(substr($nameResolveInfluencingPart, 0, -strlen('abstract')));
		}

		foreach ($objectTypes as $objectType) {
			$objectTypeType = $objectType['type'];
			$objectTypeTypeClass = $objectTypeType->getClass();
			if (preg_match('#^[a-zA-Z_\\\]#', $objectTypeTypeClass) === 0) {
				continue;
			}

			$nameResolveInfluencingPart .= sprintf("\n%s::%s;", $objectTypeTypeClass, self::CONST_FETCH_CONSTANT);
		}

		try {
			$parserNodes = $this->parser->parseString($nameResolveInfluencingPart);
		} catch (\PhpParser\Error $e) {
			throw new \PHPStan\Reflection\Php\DocCommentTypesParseErrorException($e);
		}

		$i = 0;
		$this->findClassNames($parserNodes, function ($className) use (&$typeMap, &$i, $objectTypes, $classReflection) {
			$objectType = $objectTypes[$i];
			$objectTypeString = $objectType['typeString'];
			$objectTypeType = $objectType['type'];
			$typeMap[$objectTypeString] = new ObjectType($className, $objectTypeType->isNullable());
			$i++;
		});

		$this->typeMaps[$classReflection->getName()] = $typeMap;
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

		preg_match_all('#@param\s+([a-zA-Z_\\\][0-9a-zA-Z\\\_|\[\]]+)\s+\$([a-zA-Z0-9]+)#', $phpDoc, $matches, PREG_SET_ORDER);
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

		$count = preg_match_all('#@return\s+([a-zA-Z_\\\][0-9a-zA-Z\\\_|\[\]]+)#', $phpDoc, $matches);
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

	private function getTypeFromTypeString(string $typeString, ClassReflection $classReflection): Type
	{
		$typeParts = explode('|', $typeString);
		$typePartsWithoutNull = array_values(array_filter($typeParts, function ($part) {
			return $part !== 'null';
		}));
		if (count($typePartsWithoutNull) === 0) {
			return new NullType();
		}

		if (count($typePartsWithoutNull) !== 1) {
			return new MixedType(false);
		}

		$isNullable = count($typeParts) !== count($typePartsWithoutNull);
		if ($typePartsWithoutNull[0] === 'self') {
			return new ObjectType($classReflection->getName(), $isNullable);
		}

		return TypehintHelper::getTypeObjectFromTypehint($typePartsWithoutNull[0], $isNullable);
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

		$count = preg_match_all('#@var\s+([a-zA-Z_\\\][0-9a-zA-Z\\\_|\[\]]+)#', $phpDoc, $matches);
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
			$typeMap = $this->getTypeMap($declaringClass);
			foreach ($methodReflection->getParameters() as $parameterReflection) {
				$typeString = $this->getMethodParameterAnnotationTypeString($phpDocParameters, $parameterReflection);
				if ($typeString === null || !isset($typeMap[$typeString])) {
					continue;
				}

				$type = $typeMap[$typeString];

				$phpDocParameterTypes[$parameterReflection->getName()] = $type;
			}

			$phpDocReturnType = null;
			$returnTypeString = $this->getReturnTypeStringFromMethod($methodReflection);
			if ($returnTypeString !== null) {
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

	/**
	 * @param \PhpParser\Node[]|\PhpParser\Node $node
	 * @param \Closure $callback
	 */
	private function findClassNames($node, \Closure $callback)
	{
		if ($node instanceof ClassConstFetch && $node->name === self::CONST_FETCH_CONSTANT) {
			$callback((string) $node->class);
		}

		if ($node instanceof Node) {
			foreach ($node->getSubNodeNames() as $subNodeName) {
				$subNode = $node->{$subNodeName};
				$this->findClassNames($subNode, $callback);
			}
		}

		if (is_array($node)) {
			foreach ($node as $subNode) {
				$this->findClassNames($subNode, $callback);
			}
		}
	}

}
