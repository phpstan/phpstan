<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Namespace_;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Analyser\ScopeContext;
use PHPStan\Analyser\ScopeFactory;
use PHPStan\Broker\Broker;
use PHPStan\DependencyInjection\Container;
use PHPStan\Parser\Parser;
use PHPStan\PhpDoc\PhpDocBlock;
use PHPStan\PhpDoc\Tag\ParamTag;
use PHPStan\Reflection\Annotations\AnnotationsMethodsClassReflectionExtension;
use PHPStan\Reflection\Annotations\AnnotationsPropertiesClassReflectionExtension;
use PHPStan\Reflection\BrokerAwareExtension;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionVariant;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Reflection\Native\NativeMethodReflection;
use PHPStan\Reflection\Native\NativeParameterReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Reflection\SignatureMap\ParameterSignature;
use PHPStan\Reflection\SignatureMap\SignatureMapProvider;
use PHPStan\Type\ErrorType;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\Type;
use PHPStan\Type\TypehintHelper;

class PhpClassReflectionExtension
	implements PropertiesClassReflectionExtension, MethodsClassReflectionExtension, BrokerAwareExtension
{

	/** @var \PHPStan\DependencyInjection\Container */
	private $container;

	/** @var \PHPStan\Reflection\Php\PhpMethodReflectionFactory */
	private $methodReflectionFactory;

	/** @var \PHPStan\Type\FileTypeMapper */
	private $fileTypeMapper;

	/** @var \PHPStan\Reflection\Annotations\AnnotationsMethodsClassReflectionExtension */
	private $annotationsMethodsClassReflectionExtension;

	/** @var \PHPStan\Reflection\Annotations\AnnotationsPropertiesClassReflectionExtension */
	private $annotationsPropertiesClassReflectionExtension;

	/** @var \PHPStan\Reflection\SignatureMap\SignatureMapProvider */
	private $signatureMapProvider;

	/** @var \PHPStan\Parser\Parser */
	private $parser;

	/** @var bool */
	private $inferPrivatePropertyTypeFromConstructor;

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	/** @var \PHPStan\Reflection\PropertyReflection[][] */
	private $propertiesIncludingAnnotations = [];

	/** @var \PHPStan\Reflection\Php\PhpPropertyReflection[][] */
	private $nativeProperties;

	/** @var \PHPStan\Reflection\MethodReflection[][] */
	private $methodsIncludingAnnotations = [];

	/** @var \PHPStan\Reflection\MethodReflection[][] */
	private $nativeMethods = [];

	/** @var array<string, array<string, Type>> */
	private $propertyTypesCache = [];

	/** @var array<string, true> */
	private $inferClassConstructorPropertyTypesInProcess = [];

	public function __construct(
		Container $container,
		PhpMethodReflectionFactory $methodReflectionFactory,
		FileTypeMapper $fileTypeMapper,
		AnnotationsMethodsClassReflectionExtension $annotationsMethodsClassReflectionExtension,
		AnnotationsPropertiesClassReflectionExtension $annotationsPropertiesClassReflectionExtension,
		SignatureMapProvider $signatureMapProvider,
		Parser $parser,
		bool $inferPrivatePropertyTypeFromConstructor
	)
	{
		$this->container = $container;
		$this->methodReflectionFactory = $methodReflectionFactory;
		$this->fileTypeMapper = $fileTypeMapper;
		$this->annotationsMethodsClassReflectionExtension = $annotationsMethodsClassReflectionExtension;
		$this->annotationsPropertiesClassReflectionExtension = $annotationsPropertiesClassReflectionExtension;
		$this->signatureMapProvider = $signatureMapProvider;
		$this->parser = $parser;
		$this->inferPrivatePropertyTypeFromConstructor = $inferPrivatePropertyTypeFromConstructor;
	}

	public function setBroker(Broker $broker): void
	{
		$this->broker = $broker;
	}

	public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
	{
		return $classReflection->getNativeReflection()->hasProperty($propertyName);
	}

	public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
	{
		if (!isset($this->propertiesIncludingAnnotations[$classReflection->getName()][$propertyName])) {
			$this->propertiesIncludingAnnotations[$classReflection->getName()][$propertyName] = $this->createProperty($classReflection, $propertyName, true);
		}

		return $this->propertiesIncludingAnnotations[$classReflection->getName()][$propertyName];
	}

	public function getNativeProperty(ClassReflection $classReflection, string $propertyName): PhpPropertyReflection
	{
		if (!isset($this->nativeProperties[$classReflection->getName()][$propertyName])) {
			/** @var \PHPStan\Reflection\Php\PhpPropertyReflection $property */
			$property = $this->createProperty($classReflection, $propertyName, false);
			$this->nativeProperties[$classReflection->getName()][$propertyName] = $property;
		}

		return $this->nativeProperties[$classReflection->getName()][$propertyName];
	}

	private function createProperty(
		ClassReflection $classReflection,
		string $propertyName,
		bool $includingAnnotations
	): PropertyReflection
	{
		$propertyReflection = $classReflection->getNativeReflection()->getProperty($propertyName);
		$propertyName = $propertyReflection->getName();
		$declaringClassReflection = $this->broker->getClass($propertyReflection->getDeclaringClass()->getName());
		$deprecatedDescription = null;
		$isDeprecated = false;
		$isInternal = false;

		if ($includingAnnotations && $this->annotationsPropertiesClassReflectionExtension->hasProperty($classReflection, $propertyName)) {
			$hierarchyDistances = $classReflection->getClassHierarchyDistances();
			$annotationProperty = $this->annotationsPropertiesClassReflectionExtension->getProperty($classReflection, $propertyName);
			if (!isset($hierarchyDistances[$annotationProperty->getDeclaringClass()->getName()])) {
				throw new \PHPStan\ShouldNotHappenException();
			}
			if (!isset($hierarchyDistances[$propertyReflection->getDeclaringClass()->getName()])) {
				throw new \PHPStan\ShouldNotHappenException();
			}

			if ($hierarchyDistances[$annotationProperty->getDeclaringClass()->getName()] < $hierarchyDistances[$propertyReflection->getDeclaringClass()->getName()]) {
				return $annotationProperty;
			}
		}

		$docComment = $propertyReflection->getDocComment() !== false
			? $propertyReflection->getDocComment()
			: null;

		$phpDocType = null;
		if ($declaringClassReflection->getFileName() !== false) {
			$phpDocBlock = PhpDocBlock::resolvePhpDocBlockForProperty(
				$this->broker,
				$docComment,
				$declaringClassReflection->getName(),
				null,
				$propertyName,
				$declaringClassReflection->getFileName()
			);

			if ($phpDocBlock !== null) {
				$resolvedPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc(
					$phpDocBlock->getFile(),
					$phpDocBlock->getClass(),
					$this->findPropertyTrait($phpDocBlock, $propertyReflection),
					$phpDocBlock->getDocComment()
				);
				$varTags = $resolvedPhpDoc->getVarTags();
				if (isset($varTags[0]) && count($varTags) === 1) {
					$phpDocType = $varTags[0]->getType();
				} elseif (isset($varTags[$propertyName])) {
					$phpDocType = $varTags[$propertyName]->getType();
				}
				$deprecatedDescription = $resolvedPhpDoc->getDeprecatedTag() !== null ? $resolvedPhpDoc->getDeprecatedTag()->getMessage() : null;
				$isDeprecated = $resolvedPhpDoc->isDeprecated();
				$isInternal = $resolvedPhpDoc->isInternal();
			} elseif (
				$this->inferPrivatePropertyTypeFromConstructor
				&& $propertyReflection->isPrivate()
				&& (!method_exists($propertyReflection, 'hasType') || !$propertyReflection->hasType())
				&& $declaringClassReflection->hasConstructor()
				&& $declaringClassReflection->getConstructor()->getDeclaringClass()->getName() === $declaringClassReflection->getName()
			) {
				$phpDocType = $this->inferPrivatePropertyType(
					$propertyReflection->getName(),
					$declaringClassReflection->getConstructor()
				);
			}
		}

		$nativeType = null;
		if (method_exists($propertyReflection, 'getType') && $propertyReflection->getType() !== null) {
			$nativeType = $propertyReflection->getType();
		}

		return new PhpPropertyReflection(
			$declaringClassReflection,
			$nativeType,
			$phpDocType,
			$propertyReflection,
			$deprecatedDescription,
			$isDeprecated,
			$isInternal
		);
	}

	public function hasMethod(ClassReflection $classReflection, string $methodName): bool
	{
		if (
			$classReflection->getName() === \ReflectionType::class
		) {
			$classReflection = $this->broker->getClass(\ReflectionNamedType::class);
		}

		return $classReflection->getNativeReflection()->hasMethod($methodName);
	}

	public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
	{
		if (
			$classReflection->getName() === \ReflectionType::class
		) {
			$classReflection = $this->broker->getClass(\ReflectionNamedType::class);
		}

		if (isset($this->methodsIncludingAnnotations[$classReflection->getName()][$methodName])) {
			return $this->methodsIncludingAnnotations[$classReflection->getName()][$methodName];
		}

		$nativeMethodReflection = new NativeBuiltinMethodReflection($classReflection->getNativeReflection()->getMethod($methodName));
		if (!isset($this->methodsIncludingAnnotations[$classReflection->getName()][$nativeMethodReflection->getName()])) {
			$method = $this->createMethod($classReflection, $nativeMethodReflection, true);
			$this->methodsIncludingAnnotations[$classReflection->getName()][$nativeMethodReflection->getName()] = $method;
			if ($nativeMethodReflection->getName() !== $methodName) {
				$this->methodsIncludingAnnotations[$classReflection->getName()][$methodName] = $method;
			}
		}

		return $this->methodsIncludingAnnotations[$classReflection->getName()][$nativeMethodReflection->getName()];
	}

	public function hasNativeMethod(ClassReflection $classReflection, string $methodName): bool
	{
		$hasMethod = $this->hasMethod($classReflection, $methodName);
		if ($hasMethod) {
			return true;
		}

		if ($methodName === '__get' && UniversalObjectCratesClassReflectionExtension::isUniversalObjectCrate(
			$this->broker,
			$this->broker->getUniversalObjectCratesClasses(),
			$classReflection
		)) {
			return true;
		}

		return false;
	}

	public function getNativeMethod(ClassReflection $classReflection, string $methodName): MethodReflection
	{
		if (isset($this->nativeMethods[$classReflection->getName()][$methodName])) {
			return $this->nativeMethods[$classReflection->getName()][$methodName];
		}

		if ($classReflection->getNativeReflection()->hasMethod($methodName)) {
			$nativeMethodReflection = new NativeBuiltinMethodReflection(
				$classReflection->getNativeReflection()->getMethod($methodName)
			);
		} else {
			if (
				$methodName !== '__get'
				|| !UniversalObjectCratesClassReflectionExtension::isUniversalObjectCrate(
					$this->broker,
					$this->broker->getUniversalObjectCratesClasses(),
					$classReflection
				)) {
				throw new \PHPStan\ShouldNotHappenException();
			}

			$nativeMethodReflection = new FakeBuiltinMethodReflection(
				$methodName,
				$classReflection->getNativeReflection()
			);
		}

		if (!isset($this->nativeMethods[$classReflection->getName()][$nativeMethodReflection->getName()])) {
			$method = $this->createMethod($classReflection, $nativeMethodReflection, false);
			$this->nativeMethods[$classReflection->getName()][$nativeMethodReflection->getName()] = $method;
		}

		return $this->nativeMethods[$classReflection->getName()][$nativeMethodReflection->getName()];
	}

	private function createMethod(
		ClassReflection $classReflection,
		BuiltinMethodReflection $methodReflection,
		bool $includingAnnotations
	): MethodReflection
	{
		if ($includingAnnotations && $this->annotationsMethodsClassReflectionExtension->hasMethod($classReflection, $methodReflection->getName())) {
			$hierarchyDistances = $classReflection->getClassHierarchyDistances();
			$annotationMethod = $this->annotationsMethodsClassReflectionExtension->getMethod($classReflection, $methodReflection->getName());
			if (!isset($hierarchyDistances[$annotationMethod->getDeclaringClass()->getName()])) {
				throw new \PHPStan\ShouldNotHappenException();
			}
			if (!isset($hierarchyDistances[$methodReflection->getDeclaringClass()->getName()])) {
				throw new \PHPStan\ShouldNotHappenException();
			}

			if ($hierarchyDistances[$annotationMethod->getDeclaringClass()->getName()] < $hierarchyDistances[$methodReflection->getDeclaringClass()->getName()]) {
				return $annotationMethod;
			}
		}
		$declaringClassName = $methodReflection->getDeclaringClass()->getName();
		$signatureMapMethodName = sprintf('%s::%s', $declaringClassName, $methodReflection->getName());
		$declaringClass = $this->broker->getClass($declaringClassName);
		if ($this->signatureMapProvider->hasFunctionSignature($signatureMapMethodName)) {
			$variantName = $signatureMapMethodName;
			$variants = [];
			$i = 0;
			while ($this->signatureMapProvider->hasFunctionSignature($variantName)) {
				$methodSignature = $this->signatureMapProvider->getFunctionSignature($variantName, $declaringClassName);
				$variants[] = new FunctionVariant(
					array_map(static function (ParameterSignature $parameterSignature): NativeParameterReflection {
						return new NativeParameterReflection(
							$parameterSignature->getName(),
							$parameterSignature->isOptional(),
							$parameterSignature->getType(),
							$parameterSignature->passedByReference(),
							$parameterSignature->isVariadic()
						);
					}, $methodSignature->getParameters()),
					$methodSignature->isVariadic(),
					$methodSignature->getReturnType()
				);
				$i++;
				$variantName = sprintf($signatureMapMethodName . '\'' . $i);
			}
			return new NativeMethodReflection(
				$this->broker,
				$declaringClass,
				$methodReflection,
				$variants
			);
		}

		$phpDocParameterTypes = [];
		$phpDocReturnType = null;
		$phpDocThrowType = null;
		$deprecatedDescription = null;
		$isDeprecated = false;
		$isInternal = false;
		$isFinal = false;
		$declaringTraitName = $this->findMethodTrait($methodReflection);
		if ($declaringClass->getFileName() !== false) {
			$docComment = $methodReflection->getDocComment() !== false
				? $methodReflection->getDocComment()
				: null;

			$phpDocBlock = PhpDocBlock::resolvePhpDocBlockForMethod(
				$this->broker,
				$docComment,
				$declaringClass->getName(),
				$declaringTraitName,
				$methodReflection->getName(),
				$declaringClass->getFileName()
			);

			if ($phpDocBlock !== null) {
				$resolvedPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc(
					$phpDocBlock->getFile(),
					$phpDocBlock->getClass(),
					$phpDocBlock->getTrait(),
					$phpDocBlock->getDocComment()
				);
				$phpDocParameterTypes = array_map(static function (ParamTag $tag): Type {
					return $tag->getType();
				}, $resolvedPhpDoc->getParamTags());
				$nativeReturnType = TypehintHelper::decideTypeFromReflection(
					$methodReflection->getReturnType(),
					null,
					$declaringClass->getName()
				);
				$phpDocReturnType = null;
				if (
					$resolvedPhpDoc->getReturnTag() !== null
					&& (
						$phpDocBlock->isExplicit()
						|| $nativeReturnType->isSuperTypeOf($resolvedPhpDoc->getReturnTag()->getType())->yes()
					)
				) {
					$phpDocReturnType = $resolvedPhpDoc->getReturnTag()->getType();
				}
				$phpDocThrowType = $resolvedPhpDoc->getThrowsTag() !== null ? $resolvedPhpDoc->getThrowsTag()->getType() : null;
				$deprecatedDescription = $resolvedPhpDoc->getDeprecatedTag() !== null ? $resolvedPhpDoc->getDeprecatedTag()->getMessage() : null;
				$isDeprecated = $resolvedPhpDoc->isDeprecated();
				$isInternal = $resolvedPhpDoc->isInternal();
				$isFinal = $resolvedPhpDoc->isFinal();
			}
		}

		$declaringTrait = null;
		if (
			$declaringTraitName !== null && $this->broker->hasClass($declaringTraitName)
		) {
			$declaringTrait = $this->broker->getClass($declaringTraitName);
		}

		return $this->methodReflectionFactory->create(
			$declaringClass,
			$declaringTrait,
			$methodReflection,
			$phpDocParameterTypes,
			$phpDocReturnType,
			$phpDocThrowType,
			$deprecatedDescription,
			$isDeprecated,
			$isInternal,
			$isFinal
		);
	}

	private function findPropertyTrait(
		PhpDocBlock $phpDocBlock,
		\ReflectionProperty $propertyReflection
	): ?string
	{
		$resolvedPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc(
			$phpDocBlock->getFile(),
			$phpDocBlock->getClass(),
			null,
			$phpDocBlock->getDocComment()
		);
		if (count($resolvedPhpDoc->getVarTags()) > 0) {
			return null;
		}

		$declaringClass = $propertyReflection->getDeclaringClass();
		$traits = $declaringClass->getTraits();
		while (count($traits) > 0) {
			/** @var \ReflectionClass $traitReflection */
			$traitReflection = array_pop($traits);
			$traits = array_merge($traits, $traitReflection->getTraits());
			if (!$traitReflection->hasProperty($propertyReflection->getName())) {
				continue;
			}

			$traitResolvedPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc(
				$phpDocBlock->getFile(),
				$phpDocBlock->getClass(),
				$traitReflection->getName(),
				$phpDocBlock->getDocComment()
			);
			if (
				count($traitResolvedPhpDoc->getVarTags()) > 0
			) {
				return $traitReflection->getName();
			}
		}

		return null;
	}

	private function findMethodTrait(
		BuiltinMethodReflection $methodReflection
	): ?string
	{
		$declaringClass = $methodReflection->getDeclaringClass();
		if (
			$methodReflection->getFileName() === $declaringClass->getFileName()
			&& $methodReflection->getStartLine() >= $declaringClass->getStartLine()
			&& $methodReflection->getEndLine() <= $declaringClass->getEndLine()
		) {
			return null;
		}

		$declaringClass = $methodReflection->getDeclaringClass();
		$traitAliases = $declaringClass->getTraitAliases();
		if (array_key_exists($methodReflection->getName(), $traitAliases)) {
			return explode('::', $traitAliases[$methodReflection->getName()])[0];
		}

		foreach ($this->collectTraits($declaringClass) as $traitReflection) {
			if (!$traitReflection->hasMethod($methodReflection->getName())) {
				continue;
			}

			if (
				$methodReflection->getFileName() === $traitReflection->getFileName()
				&& $methodReflection->getStartLine() >= $traitReflection->getStartLine()
				&& $methodReflection->getEndLine() <= $traitReflection->getEndLine()
			) {
				return $traitReflection->getName();
			}
		}

		return null;
	}

	/**
	 * @param \ReflectionClass $class
	 * @return \ReflectionClass[]
	 */
	private function collectTraits(\ReflectionClass $class): array
	{
		$traits = [];
		$traitsLeftToAnalyze = $class->getTraits();

		while (count($traitsLeftToAnalyze) !== 0) {
			$trait = reset($traitsLeftToAnalyze);
			$traits[] = $trait;

			foreach ($trait->getTraits() as $subTrait) {
				if (in_array($subTrait, $traits, true)) {
					continue;
				}

				$traitsLeftToAnalyze[] = $subTrait;
			}

			array_shift($traitsLeftToAnalyze);
		}

		return $traits;
	}

	private function inferPrivatePropertyType(
		string $propertyName,
		MethodReflection $constructor
	): Type
	{
		$declaringClassName = $constructor->getDeclaringClass()->getName();
		if (isset($this->inferClassConstructorPropertyTypesInProcess[$declaringClassName])) {
			return new MixedType();
		}
		$this->inferClassConstructorPropertyTypesInProcess[$declaringClassName] = true;
		$propertyTypes = $this->inferAndCachePropertyTypes($constructor);
		unset($this->inferClassConstructorPropertyTypesInProcess[$declaringClassName]);
		if (array_key_exists($propertyName, $propertyTypes)) {
			return $propertyTypes[$propertyName];
		}

		return new MixedType();
	}

	/**
	 * @param \PHPStan\Reflection\MethodReflection $constructor
	 * @return array<string, Type>
	 */
	private function inferAndCachePropertyTypes(
		MethodReflection $constructor
	): array
	{
		$declaringClass = $constructor->getDeclaringClass();
		if (isset($this->propertyTypesCache[$declaringClass->getName()])) {
			return $this->propertyTypesCache[$declaringClass->getName()];
		}
		if ($declaringClass->getFileName() === false) {
			return $this->propertyTypesCache[$declaringClass->getName()] = [];
		}

		$fileName = $declaringClass->getFileName();
		$nodes = $this->parser->parseFile($fileName);
		$classNode = $this->findClassNode($declaringClass->getName(), $nodes);
		if ($classNode === null) {
			return $this->propertyTypesCache[$declaringClass->getName()] = [];
		}

		$methodNode = $this->findConstructorNode($constructor->getName(), $classNode->stmts);
		if ($methodNode === null || $methodNode->stmts === null) {
			return $this->propertyTypesCache[$declaringClass->getName()] = [];
		}

		/** @var NodeScopeResolver $nodeScopeResolver */
		$nodeScopeResolver = $this->container->getByType(NodeScopeResolver::class);

		/** @var \PHPStan\Analyser\ScopeFactory $scopeFactory */
		$scopeFactory = $this->container->getByType(ScopeFactory::class);

		$classNameParts = explode('\\', $declaringClass->getName());
		$namespace = null;
		if (count($classNameParts) > 0) {
			$namespace = implode('\\', array_slice($classNameParts, 0, -1));
		}

		$classScope = $scopeFactory->create(
			ScopeContext::create($fileName),
			false,
			$constructor,
			$namespace
		)->enterClass($declaringClass);
		[$phpDocParameterTypes, $phpDocReturnType, $phpDocThrowType, $deprecatedDescription, $isDeprecated, $isInternal, $isFinal] = $nodeScopeResolver->getPhpDocs($classScope, $methodNode);
		$methodScope = $classScope->enterClassMethod(
			$methodNode,
			$phpDocParameterTypes,
			$phpDocReturnType,
			$phpDocThrowType,
			$deprecatedDescription,
			$isDeprecated,
			$isInternal,
			$isFinal
		);

		$propertyTypes = [];
		foreach ($methodNode->stmts as $statement) {
			if (!$statement instanceof Node\Stmt\Expression) {
				continue;
			}

			$expr = $statement->expr;
			if (!$expr instanceof Node\Expr\Assign) {
				continue;
			}

			if (!$expr->var instanceof Node\Expr\PropertyFetch) {
				continue;
			}

			$propertyFetch = $expr->var;
			if (
				!$propertyFetch->var instanceof Node\Expr\Variable
				|| $propertyFetch->var->name !== 'this'
				|| !$propertyFetch->name instanceof Node\Identifier
			) {
				continue;
			}

			$propertyType = $methodScope->getType($expr->expr);
			if ($propertyType instanceof ErrorType || $propertyType instanceof NeverType) {
				continue;
			}

			$propertyTypes[$propertyFetch->name->toString()] = $propertyType;
		}

		return $this->propertyTypesCache[$declaringClass->getName()] = $propertyTypes;
	}

	/**
	 * @param string $className
	 * @param \PhpParser\Node[] $nodes
	 * @return \PhpParser\Node\Stmt\Class_|null
	 */
	private function findClassNode(string $className, array $nodes): ?Class_
	{
		foreach ($nodes as $node) {
			if (
				$node instanceof Class_
				&& $node->namespacedName->toString() === $className
			) {
				return $node;
			}
			if (
				!$node instanceof Namespace_
				&& !$node instanceof Declare_
			) {
				continue;
			}
			$subNodeNames = $node->getSubNodeNames();
			foreach ($subNodeNames as $subNodeName) {
				$subNode = $node->{$subNodeName};
				if (!is_array($subNode)) {
					$subNode = [$subNode];
				}
				$result = $this->findClassNode($className, $subNode);
				if ($result === null) {
					continue;
				}
				return $result;
			}
		}
		return null;
	}

	/**
	 * @param string $methodName
	 * @param \PhpParser\Node\Stmt[] $classStatements
	 * @return \PhpParser\Node\Stmt\ClassMethod|null
	 */
	private function findConstructorNode(string $methodName, array $classStatements): ?ClassMethod
	{
		foreach ($classStatements as $statement) {
			if (
				$statement instanceof ClassMethod
				&& $statement->name->toString() === $methodName
			) {
				return $statement;
			}
		}
		return null;
	}

}
