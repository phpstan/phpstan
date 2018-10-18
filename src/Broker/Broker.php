<?php declare(strict_types = 1);

namespace PHPStan\Broker;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\File\RelativePathHelper;
use PHPStan\Parser\Parser;
use PHPStan\PhpDoc\Tag\ParamTag;
use PHPStan\Reflection\BrokerAwareExtension;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionReflectionFactory;
use PHPStan\Reflection\FunctionVariant;
use PHPStan\Reflection\Native\NativeFunctionReflection;
use PHPStan\Reflection\Native\NativeParameterReflection;
use PHPStan\Reflection\SignatureMap\ParameterSignature;
use PHPStan\Reflection\SignatureMap\SignatureMapProvider;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\NullType;
use PHPStan\Type\StringAlwaysAcceptingObjectWithToStringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\UnionType;
use ReflectionClass;

class Broker
{

	/** @var \PHPStan\Reflection\PropertiesClassReflectionExtension[] */
	private $propertiesClassReflectionExtensions;

	/** @var \PHPStan\Reflection\MethodsClassReflectionExtension[] */
	private $methodsClassReflectionExtensions;

	/** @var \PHPStan\Type\DynamicMethodReturnTypeExtension[] */
	private $dynamicMethodReturnTypeExtensions = [];

	/** @var \PHPStan\Type\DynamicStaticMethodReturnTypeExtension[] */
	private $dynamicStaticMethodReturnTypeExtensions = [];

	/** @var \PHPStan\Type\DynamicMethodReturnTypeExtension[][]|null */
	private $dynamicMethodReturnTypeExtensionsByClass;

	/** @var \PHPStan\Type\DynamicStaticMethodReturnTypeExtension[][]|null */
	private $dynamicStaticMethodReturnTypeExtensionsByClass;

	/** @var \PHPStan\Type\DynamicFunctionReturnTypeExtension[] */
	private $dynamicFunctionReturnTypeExtensions = [];

	/** @var \PHPStan\Reflection\ClassReflection[] */
	private $classReflections = [];

	/** @var \PHPStan\Reflection\FunctionReflectionFactory */
	private $functionReflectionFactory;

	/** @var \PHPStan\Type\FileTypeMapper */
	private $fileTypeMapper;

	/** @var \PHPStan\Reflection\SignatureMap\SignatureMapProvider */
	private $signatureMapProvider;

	/** @var \PhpParser\PrettyPrinter\Standard */
	private $printer;

	/** @var AnonymousClassNameHelper */
	private $anonymousClassNameHelper;

	/** @var Parser */
	private $parser;

	/** @var RelativePathHelper */
	private $relativePathHelper;

	/** @var string[] */
	private $universalObjectCratesClasses;

	/** @var \PHPStan\Reflection\FunctionReflection[] */
	private $functionReflections = [];

	/** @var \PHPStan\Reflection\Php\PhpFunctionReflection[] */
	private $customFunctionReflections = [];

	/** @var self|null */
	private static $instance;

	/** @var bool[] */
	private $hasClassCache;

	/** @var NativeFunctionReflection[] */
	private static $functionMap = [];

	/** @var \PHPStan\Reflection\ClassReflection[] */
	private static $anonymousClasses = [];

	/**
	 * @param \PHPStan\Reflection\PropertiesClassReflectionExtension[] $propertiesClassReflectionExtensions
	 * @param \PHPStan\Reflection\MethodsClassReflectionExtension[] $methodsClassReflectionExtensions
	 * @param \PHPStan\Type\DynamicMethodReturnTypeExtension[] $dynamicMethodReturnTypeExtensions
	 * @param \PHPStan\Type\DynamicStaticMethodReturnTypeExtension[] $dynamicStaticMethodReturnTypeExtensions
	 * @param \PHPStan\Type\DynamicFunctionReturnTypeExtension[] $dynamicFunctionReturnTypeExtensions
	 * @param \PHPStan\Reflection\FunctionReflectionFactory $functionReflectionFactory
	 * @param \PHPStan\Type\FileTypeMapper $fileTypeMapper
	 * @param \PHPStan\Reflection\SignatureMap\SignatureMapProvider $signatureMapProvider
	 * @param \PhpParser\PrettyPrinter\Standard $printer
	 * @param AnonymousClassNameHelper $anonymousClassNameHelper
	 * @param Parser $parser
	 * @param RelativePathHelper $relativePathHelper
	 * @param string[] $universalObjectCratesClasses
	 */
	public function __construct(
		array $propertiesClassReflectionExtensions,
		array $methodsClassReflectionExtensions,
		array $dynamicMethodReturnTypeExtensions,
		array $dynamicStaticMethodReturnTypeExtensions,
		array $dynamicFunctionReturnTypeExtensions,
		FunctionReflectionFactory $functionReflectionFactory,
		FileTypeMapper $fileTypeMapper,
		SignatureMapProvider $signatureMapProvider,
		\PhpParser\PrettyPrinter\Standard $printer,
		AnonymousClassNameHelper $anonymousClassNameHelper,
		Parser $parser,
		RelativePathHelper $relativePathHelper,
		array $universalObjectCratesClasses
	)
	{
		$this->propertiesClassReflectionExtensions = $propertiesClassReflectionExtensions;
		$this->methodsClassReflectionExtensions = $methodsClassReflectionExtensions;
		foreach (array_merge($propertiesClassReflectionExtensions, $methodsClassReflectionExtensions, $dynamicMethodReturnTypeExtensions, $dynamicStaticMethodReturnTypeExtensions, $dynamicFunctionReturnTypeExtensions) as $extension) {
			if (!($extension instanceof BrokerAwareExtension)) {
				continue;
			}

			$extension->setBroker($this);
		}

		$this->dynamicMethodReturnTypeExtensions = $dynamicMethodReturnTypeExtensions;
		$this->dynamicStaticMethodReturnTypeExtensions = $dynamicStaticMethodReturnTypeExtensions;

		foreach ($dynamicFunctionReturnTypeExtensions as $functionReturnTypeExtension) {
			$this->dynamicFunctionReturnTypeExtensions[] = $functionReturnTypeExtension;
		}

		$this->functionReflectionFactory = $functionReflectionFactory;
		$this->fileTypeMapper = $fileTypeMapper;
		$this->signatureMapProvider = $signatureMapProvider;
		$this->printer = $printer;
		$this->anonymousClassNameHelper = $anonymousClassNameHelper;
		$this->parser = $parser;
		$this->relativePathHelper = $relativePathHelper;
		$this->universalObjectCratesClasses = $universalObjectCratesClasses;
	}

	public static function registerInstance(Broker $broker): void
	{
		self::$instance = $broker;
	}

	public static function getInstance(): self
	{
		if (self::$instance === null) {
			throw new \PHPStan\ShouldNotHappenException();
		}
		return self::$instance;
	}

	/**
	 * @return string[]
	 */
	public function getUniversalObjectCratesClasses(): array
	{
		return $this->universalObjectCratesClasses;
	}

	/**
	 * @param string $className
	 * @return \PHPStan\Type\DynamicMethodReturnTypeExtension[]
	 */
	public function getDynamicMethodReturnTypeExtensionsForClass(string $className): array
	{
		if ($this->dynamicMethodReturnTypeExtensionsByClass === null) {
			$byClass = [];
			foreach ($this->dynamicMethodReturnTypeExtensions as $extension) {
				$byClass[$extension->getClass()][] = $extension;
			}

			$this->dynamicMethodReturnTypeExtensionsByClass = $byClass;
		}
		return $this->getDynamicExtensionsForType($this->dynamicMethodReturnTypeExtensionsByClass, $className);
	}

	/**
	 * @param string $className
	 * @return \PHPStan\Type\DynamicStaticMethodReturnTypeExtension[]
	 */
	public function getDynamicStaticMethodReturnTypeExtensionsForClass(string $className): array
	{
		if ($this->dynamicStaticMethodReturnTypeExtensionsByClass === null) {
			$byClass = [];
			foreach ($this->dynamicStaticMethodReturnTypeExtensions as $extension) {
				$byClass[$extension->getClass()][] = $extension;
			}

			$this->dynamicStaticMethodReturnTypeExtensionsByClass = $byClass;
		}
		return $this->getDynamicExtensionsForType($this->dynamicStaticMethodReturnTypeExtensionsByClass, $className);
	}

	/**
	 * @return \PHPStan\Type\DynamicFunctionReturnTypeExtension[]
	 */
	public function getDynamicFunctionReturnTypeExtensions(): array
	{
		return $this->dynamicFunctionReturnTypeExtensions;
	}

	/**
	 * @param \PHPStan\Type\DynamicMethodReturnTypeExtension[][]|\PHPStan\Type\DynamicStaticMethodReturnTypeExtension[][] $extensions
	 * @param string $className
	 * @return mixed[]
	 */
	private function getDynamicExtensionsForType(array $extensions, string $className): array
	{
		$extensionsForClass = [];
		$class = $this->getClass($className);
		foreach (array_merge([$className], $class->getParentClassesNames(), $class->getNativeReflection()->getInterfaceNames()) as $extensionClassName) {
			if (!isset($extensions[$extensionClassName])) {
				continue;
			}

			$extensionsForClass = array_merge($extensionsForClass, $extensions[$extensionClassName]);
		}

		return $extensionsForClass;
	}

	public function getClass(string $className): \PHPStan\Reflection\ClassReflection
	{
		if (!$this->hasClass($className)) {
			throw new \PHPStan\Broker\ClassNotFoundException($className);
		}

		if (isset(self::$anonymousClasses[$className])) {
			return self::$anonymousClasses[$className];
		}

		if (!isset($this->classReflections[$className])) {
			$reflectionClass = new ReflectionClass($className);
			$filename = null;
			if ($reflectionClass->getFileName() !== false) {
				$filename = $reflectionClass->getFileName();
			}

			$classReflection = $this->getClassFromReflection(
				$reflectionClass,
				$reflectionClass->getName(),
				$reflectionClass->isAnonymous() ? $filename : null
			);
			$this->classReflections[$className] = $classReflection;
			if ($className !== $reflectionClass->getName()) {
				// class alias optimization
				$this->classReflections[$reflectionClass->getName()] = $classReflection;
			}
		}

		return $this->classReflections[$className];
	}

	public function getAnonymousClassReflection(
		\PhpParser\Node\Expr\New_ $node,
		Scope $scope
	): ClassReflection
	{
		if (!$node->class instanceof \PhpParser\Node\Stmt\Class_) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		if (!$scope->isInTrait()) {
			$scopeFile = $scope->getFile();
		} else {
			$scopeFile = $scope->getTraitReflection()->getFileName();
			if ($scopeFile === false) {
				$scopeFile = $scope->getFile();
			}
		}

		$filename = $this->relativePathHelper->getRelativePath($scopeFile);

		$className = $this->anonymousClassNameHelper->getAnonymousClassName(
			$node,
			$filename
		);

		if (isset(self::$anonymousClasses[$className])) {
			return self::$anonymousClasses[$className];
		}

		$classNode = $node->class;
		$classNode->name = new \PhpParser\Node\Identifier($className);
		eval($this->printer->prettyPrint([$classNode]));
		unset($classNode);

		self::$anonymousClasses[$className] = $this->getClassFromReflection(
			new \ReflectionClass('\\' . $className),
			sprintf('class@anonymous/%s:%s', $filename, $node->getLine()),
			$scopeFile
		);
		$this->classReflections[$className] = self::$anonymousClasses[$className];

		return self::$anonymousClasses[$className];
	}

	public function getClassFromReflection(\ReflectionClass $reflectionClass, string $displayName, ?string $anonymousFilename): ClassReflection
	{
		$className = $reflectionClass->getName();
		if (!isset($this->classReflections[$className])) {
			$classReflection = new ClassReflection(
				$this,
				$this->fileTypeMapper,
				$this->propertiesClassReflectionExtensions,
				$this->methodsClassReflectionExtensions,
				$displayName,
				$reflectionClass,
				$anonymousFilename
			);
			$this->classReflections[$className] = $classReflection;
		}

		return $this->classReflections[$className];
	}

	public function hasClass(string $className): bool
	{
		if (isset($this->hasClassCache[$className])) {
			return $this->hasClassCache[$className];
		}

		spl_autoload_register($autoloader = function (string $autoloadedClassName) use ($className): void {
			if ($autoloadedClassName !== $className && !$this->isExistsCheckCall()) {
				throw new \PHPStan\Broker\ClassAutoloadingException($autoloadedClassName);
			}
		});

		try {
			return $this->hasClassCache[$className] = class_exists($className) || interface_exists($className) || trait_exists($className);
		} catch (\PHPStan\Broker\ClassAutoloadingException $e) {
			throw $e;
		} catch (\Throwable $t) {
			throw new \PHPStan\Broker\ClassAutoloadingException(
				$className,
				$t
			);
		} finally {
			spl_autoload_unregister($autoloader);
		}
	}

	public function getFunction(\PhpParser\Node\Name $nameNode, ?Scope $scope): \PHPStan\Reflection\FunctionReflection
	{
		$functionName = $this->resolveFunctionName($nameNode, $scope);
		if ($functionName === null) {
			throw new \PHPStan\Broker\FunctionNotFoundException((string) $nameNode);
		}

		$lowerCasedFunctionName = strtolower($functionName);
		if (!isset($this->functionReflections[$lowerCasedFunctionName])) {
			if (isset(self::$functionMap[$lowerCasedFunctionName])) {
				return $this->functionReflections[$lowerCasedFunctionName] = self::$functionMap[$lowerCasedFunctionName];
			}

			if ($this->signatureMapProvider->hasFunctionSignature($lowerCasedFunctionName)) {
				$variantName = $lowerCasedFunctionName;
				$variants = [];
				$i = 0;
				while ($this->signatureMapProvider->hasFunctionSignature($variantName)) {
					$functionSignature = $this->signatureMapProvider->getFunctionSignature($variantName, null);
					$returnType = $functionSignature->getReturnType();
					if ($lowerCasedFunctionName === 'pow') {
						$returnType = TypeUtils::toBenevolentUnion($returnType);
					}
					$variants[] = new FunctionVariant(
						array_map(static function (ParameterSignature $parameterSignature) use ($lowerCasedFunctionName): NativeParameterReflection {
							$type = $parameterSignature->getType();
							if (
								$parameterSignature->getName() === 'args'
								&& (
									$lowerCasedFunctionName === 'printf'
									|| $lowerCasedFunctionName === 'sprintf'
								)
							) {
								$type = new UnionType([
									new StringAlwaysAcceptingObjectWithToStringType(),
									new IntegerType(),
									new FloatType(),
									new NullType(),
									new BooleanType(),
								]);
							}
							return new NativeParameterReflection(
								$parameterSignature->getName(),
								$parameterSignature->isOptional(),
								$type,
								$parameterSignature->passedByReference(),
								$parameterSignature->isVariadic()
							);
						}, $functionSignature->getParameters()),
						$functionSignature->isVariadic(),
						$returnType
					);

					$i++;
					$variantName = sprintf($lowerCasedFunctionName . '\'' . $i);
				}
				$functionReflection = new NativeFunctionReflection(
					$lowerCasedFunctionName,
					$variants,
					null
				);
				self::$functionMap[$lowerCasedFunctionName] = $functionReflection;
				$this->functionReflections[$lowerCasedFunctionName] = $functionReflection;
			} else {
				$this->functionReflections[$lowerCasedFunctionName] = $this->getCustomFunction($nameNode, $scope);
			}
		}

		return $this->functionReflections[$lowerCasedFunctionName];
	}

	public function hasFunction(\PhpParser\Node\Name $nameNode, ?Scope $scope): bool
	{
		return $this->resolveFunctionName($nameNode, $scope) !== null;
	}

	public function hasCustomFunction(\PhpParser\Node\Name $nameNode, ?Scope $scope): bool
	{
		$functionName = $this->resolveFunctionName($nameNode, $scope);
		if ($functionName === null) {
			return false;
		}

		$lowerCasedFunctionName = strtolower($functionName);

		return !$this->signatureMapProvider->hasFunctionSignature($lowerCasedFunctionName);
	}

	public function getCustomFunction(\PhpParser\Node\Name $nameNode, ?Scope $scope): \PHPStan\Reflection\Php\PhpFunctionReflection
	{
		if (!$this->hasCustomFunction($nameNode, $scope)) {
			throw new \PHPStan\Broker\FunctionNotFoundException((string) $nameNode);
		}

		/** @var string $functionName */
		$functionName = $this->resolveFunctionName($nameNode, $scope);
		if (!function_exists($functionName)) {
			throw new \PHPStan\Broker\FunctionNotFoundException($functionName);
		}
		$lowerCasedFunctionName = strtolower($functionName);
		if (isset($this->customFunctionReflections[$lowerCasedFunctionName])) {
			return $this->customFunctionReflections[$lowerCasedFunctionName];
		}

		$reflectionFunction = new \ReflectionFunction($functionName);
		$phpDocParameterTags = [];
		$phpDocReturnTag = null;
		$phpDocThrowsTag = null;
		$isDeprecated = false;
		$isInternal = false;
		$isFinal = false;
		if ($reflectionFunction->getFileName() !== false && $reflectionFunction->getDocComment() !== false) {
			$fileName = $reflectionFunction->getFileName();
			$docComment = $reflectionFunction->getDocComment();
			$resolvedPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc($fileName, null, null, $docComment);
			$phpDocParameterTags = $resolvedPhpDoc->getParamTags();
			$phpDocReturnTag = $resolvedPhpDoc->getReturnTag();
			$phpDocThrowsTag = $resolvedPhpDoc->getThrowsTag();
			$isDeprecated = $resolvedPhpDoc->isDeprecated();
			$isInternal = $resolvedPhpDoc->isInternal();
			$isFinal = $resolvedPhpDoc->isFinal();
		}

		$functionReflection = $this->functionReflectionFactory->create(
			$reflectionFunction,
			array_map(static function (ParamTag $paramTag): Type {
				return $paramTag->getType();
			}, $phpDocParameterTags),
			$phpDocReturnTag !== null ? $phpDocReturnTag->getType() : null,
			$phpDocThrowsTag !== null ? $phpDocThrowsTag->getType() : null,
			$isDeprecated,
			$isInternal,
			$isFinal,
			$reflectionFunction->getFileName()
		);
		$this->customFunctionReflections[$lowerCasedFunctionName] = $functionReflection;

		return $functionReflection;
	}

	public function resolveFunctionName(\PhpParser\Node\Name $nameNode, ?Scope $scope): ?string
	{
		return $this->resolveName($nameNode, static function (string $name): bool {
			$exists = function_exists($name);
			if ($exists) {
				return true;
			}

			$lowercased = strtolower($name);
			if ($lowercased === 'getallheaders') {
				return true;
			}
			if (\Nette\Utils\Strings::startsWith($lowercased, 'apache_')) {
				return true;
			}
			if (\Nette\Utils\Strings::startsWith($lowercased, 'fastcgi_')) {
				return true;
			}
			if (\Nette\Utils\Strings::startsWith($lowercased, 'xdebug_')) {
				return true;
			}

			return false;
		}, $scope);
	}

	public function hasConstant(\PhpParser\Node\Name $nameNode, ?Scope $scope): bool
	{
		return $this->resolveConstantName($nameNode, $scope) !== null;
	}

	public function resolveConstantName(\PhpParser\Node\Name $nameNode, ?Scope $scope): ?string
	{
		return $this->resolveName($nameNode, function (string $name) use ($scope): bool {
			$isCompilerHaltOffset = $name === '__COMPILER_HALT_OFFSET__';
			if ($isCompilerHaltOffset && $scope !== null && $this->fileHasCompilerHaltStatementCalls($scope->getFile())) {
				return true;
			}
			return defined($name);
		}, $scope);
	}

	private function fileHasCompilerHaltStatementCalls(string $pathToFile): bool
	{
		$nodes = $this->parser->parseFile($pathToFile);
		foreach ($nodes as $node) {
			if ($node instanceof Node\Stmt\HaltCompiler) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param Node\Name $nameNode
	 * @param \Closure(string $name): bool $existsCallback
	 * @param Scope|null $scope
	 * @return string|null
	 */
	private function resolveName(
		\PhpParser\Node\Name $nameNode,
		\Closure $existsCallback,
		?Scope $scope
	): ?string
	{
		$name = (string) $nameNode;
		if ($scope !== null && $scope->getNamespace() !== null && !$nameNode->isFullyQualified()) {
			$namespacedName = sprintf('%s\\%s', $scope->getNamespace(), $name);
			if ($existsCallback($namespacedName)) {
				return $namespacedName;
			}
		}

		if ($existsCallback($name)) {
			return $name;
		}

		return null;
	}

	private function isExistsCheckCall(): bool
	{
		$debugBacktrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		$existsCallTypes = [
			'class_exists' => true,
			'interface_exists' => true,
			'trait_exists' => true,
		];

		foreach ($debugBacktrace as $traceStep) {
			if (
				isset($traceStep['function'])
				&& isset($existsCallTypes[$traceStep['function']])
				// We must ignore the self::hasClass calls
				&& (!isset($traceStep['file']) || $traceStep['file'] !== __FILE__)
			) {
				return true;
			}
		}

		return false;
	}

}
