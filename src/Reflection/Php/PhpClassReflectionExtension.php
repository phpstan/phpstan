<?php declare(strict_types=1);

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
			$typeString = $this->getPropertyAnnotationType($propertyReflection);
			if ($typeString === null) {
				$type = new MixedType(false);
			} else {
				$typeMap = $this->getTypeMap($declaringClassReflection);
				$type = $typeMap[$typeString];
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
		$declaringClasses = [];
		foreach ($classReflection->getNativeReflection()->getProperties() as $propertyReflection) {
			$declaringClass = $propertyReflection->getDeclaringClass();
			if ($classReflection->getName() !== $declaringClass->getName()) {
				$this->createTypeMap($this->broker->getClass($declaringClass->getName()));
				continue;
			}
			$declaringClasses[$declaringClass->getName()] = $declaringClass;
			if (!isset($typeMap[$declaringClass->getName()])) {
				$typeMap[$declaringClass->getName()] = [];
			}

			$typeString = $this->getPropertyAnnotationType($propertyReflection);
			if ($typeString === null) {
				continue;
			}
			$typeParts = explode('|', $typeString);
			$typePartsWithoutNull = array_values(array_filter($typeParts, function ($part) {
				return $part !== 'null';
			}));
			if (count($typePartsWithoutNull) === 0) {
				$typeMap[$declaringClass->getName()][$typeString] = new NullType();
				continue;
			}
			if (count($typePartsWithoutNull) !== 1) {
				$typeMap[$declaringClass->getName()][$typeString] = new MixedType(false);
				continue;
			}
			$isNullable = count($typeParts) !== count($typePartsWithoutNull);
			if ($typePartsWithoutNull[0] === 'self') {
				$typeMap[$declaringClass->getName()][$typeString] = new ObjectType($declaringClass->getName(), $isNullable);
				continue;
			}
			$type = TypehintHelper::getTypeObjectFromTypehint($typePartsWithoutNull[0], $isNullable);
			if (!($type instanceof ObjectType)) {
				$typeMap[$declaringClass->getName()][$typeString] = $type;
				continue;
			}

			$objectTypes[$declaringClass->getName()][] = [
				'type' => $type,
				'typeString' => $typeString,
			];
		}

		// todo opět zploštit, $declaringClasses je tu zase jen jedna

		foreach ($declaringClasses as $declaringClassName => $declaringClass) {
			if (!isset($objectTypes[$declaringClassName])) {
				$this->typeMaps[$declaringClassName] = $typeMap[$declaringClassName];
				continue;
			}

			$classFileString = file_get_contents($declaringClass->getFileName());
			$classType = 'class';
			if ($declaringClass->isInterface()) {
				$classType = 'interface';
			} elseif ($declaringClass->isTrait()) {
				$classType = 'trait';
			}
			$classTypePosition = strpos($classFileString, sprintf('%s %s', $classType, $declaringClass->getShortName()));
			if ($classTypePosition === false) {
				throw new \Exception('wtf?'); // todo
			}
			$nameResolveInfluencingPart = trim(substr($classFileString, 0, $classTypePosition));
			if (substr($nameResolveInfluencingPart, -strlen('final')) === 'final') {
				$nameResolveInfluencingPart = trim(substr($nameResolveInfluencingPart, 0, -strlen('final')));
			}
			if (substr($nameResolveInfluencingPart, -strlen('abstract')) === 'abstract') {
				$nameResolveInfluencingPart = trim(substr($nameResolveInfluencingPart, 0, -strlen('abstract')));
			}

			foreach ($objectTypes[$declaringClassName] as $objectType) {
				$objectTypeType = $objectType['type'];
				$nameResolveInfluencingPart .= sprintf("\n%s::%s;", $objectTypeType->getClass(), self::CONST_FETCH_CONSTANT);
			}

			try {
				$parserNodes = $this->parser->parseString($nameResolveInfluencingPart);
			} catch (\PhpParser\Error $e) {
				throw new \PHPStan\Reflection\Php\DocCommentTypesParseErrorException($e);
			}
			$i = 0;
			$this->findClassNames($parserNodes, function ($className) use (&$typeMap, &$i, $objectTypes, $declaringClassName) {
				$objectType = $objectTypes[$declaringClassName][$i];
				$objectTypeString = $objectType['typeString'];
				$objectTypeType = $objectType['type'];
				$typeMap[$declaringClassName][$objectTypeString] = new ObjectType($className, $objectTypeType->isNullable());
				$i++;
			});

			$this->typeMaps[$declaringClassName] = $typeMap[$declaringClassName];
		}
	}

	/**
	 * @param \ReflectionProperty $propertyReflection
	 * @return string|null
	 */
	private function getPropertyAnnotationType(\ReflectionProperty $propertyReflection)
	{
		$phpDoc = $propertyReflection->getDocComment();
		if ($phpDoc === false) {
			return null;
		}
		$count = preg_match_all('#@var\s+([0-9a-zA-Z\\\_|\[\]]+)#', $phpDoc, $matches);
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
		if (!isset($this->methods[$classReflection->getName()][$methodName])) {
			$methodReflection = $classReflection->getNativeReflection()->getMethod($methodName);
			$this->methods[$classReflection->getName()][$methodName] = $this->methodReflectionFactory->create(
				$this->broker->getClass($methodReflection->getDeclaringClass()->getName()),
				$methodReflection
			);
		}

		return $this->methods[$classReflection->getName()][$methodName];
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
