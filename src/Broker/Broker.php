<?php declare(strict_types = 1);

namespace PHPStan\Broker;

use PHPStan\Analyser\Scope;
use PHPStan\PhpDoc\Tag\ParamTag;
use PHPStan\Reflection\BrokerAwareExtension;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionReflectionFactory;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\Type;
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

	/** @var \PHPStan\Type\DynamicMethodReturnTypeExtension[] */
	private $dynamicMethodReturnTypeExtensionsByClass;

	/** @var \PHPStan\Type\DynamicStaticMethodReturnTypeExtension[] */
	private $dynamicStaticMethodReturnTypeExtensionsByClass;

	/** @var \PHPStan\Type\DynamicFunctionReturnTypeExtension[] */
	private $dynamicFunctionReturnTypeExtensions = [];

	/** @var \PHPStan\Reflection\ClassReflection[] */
	private $classReflections = [];

	/** @var \PHPStan\Reflection\FunctionReflectionFactory */
	private $functionReflectionFactory;

	/** @var \PHPStan\Type\FileTypeMapper */
	private $fileTypeMapper;

	/** @var \PHPStan\Reflection\FunctionReflection[] */
	private $functionReflections = [];

	/** @var null|self */
	private static $instance;

	/** @var bool[] */
	private $hasClassCache;

	/**
	 * @param \PHPStan\Reflection\PropertiesClassReflectionExtension[] $propertiesClassReflectionExtensions
	 * @param \PHPStan\Reflection\MethodsClassReflectionExtension[] $methodsClassReflectionExtensions
	 * @param \PHPStan\Type\DynamicMethodReturnTypeExtension[] $dynamicMethodReturnTypeExtensions
	 * @param \PHPStan\Type\DynamicStaticMethodReturnTypeExtension[] $dynamicStaticMethodReturnTypeExtensions
	 * @param \PHPStan\Type\DynamicFunctionReturnTypeExtension[] $dynamicFunctionReturnTypeExtensions
	 * @param \PHPStan\Reflection\FunctionReflectionFactory $functionReflectionFactory
	 * @param \PHPStan\Type\FileTypeMapper $fileTypeMapper
	 */
	public function __construct(
		array $propertiesClassReflectionExtensions,
		array $methodsClassReflectionExtensions,
		array $dynamicMethodReturnTypeExtensions,
		array $dynamicStaticMethodReturnTypeExtensions,
		array $dynamicFunctionReturnTypeExtensions,
		FunctionReflectionFactory $functionReflectionFactory,
		FileTypeMapper $fileTypeMapper
	)
	{
		$this->propertiesClassReflectionExtensions = $propertiesClassReflectionExtensions;
		$this->methodsClassReflectionExtensions = $methodsClassReflectionExtensions;
		foreach (array_merge($propertiesClassReflectionExtensions, $methodsClassReflectionExtensions, $dynamicMethodReturnTypeExtensions, $dynamicStaticMethodReturnTypeExtensions, $dynamicFunctionReturnTypeExtensions) as $extension) {
			if ($extension instanceof BrokerAwareExtension) {
				$extension->setBroker($this);
			}
		}

		$this->dynamicMethodReturnTypeExtensions = $dynamicMethodReturnTypeExtensions;
		$this->dynamicStaticMethodReturnTypeExtensions = $dynamicStaticMethodReturnTypeExtensions;

		foreach ($dynamicFunctionReturnTypeExtensions as $functionReturnTypeExtension) {
			$this->dynamicFunctionReturnTypeExtensions[] = $functionReturnTypeExtension;
		}

		$this->functionReflectionFactory = $functionReflectionFactory;
		$this->fileTypeMapper = $fileTypeMapper;

		self::$instance = $this;
	}

	public static function getInstance(): self
	{
		if (self::$instance === null) {
			throw new \PHPStan\ShouldNotHappenException();
		}
		return self::$instance;
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
	 * @param \PHPStan\Type\DynamicMethodReturnTypeExtension[]|\PHPStan\Type\DynamicStaticMethodReturnTypeExtension[] $extensions
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

		if (!isset($this->classReflections[$className])) {
			$reflectionClass = new ReflectionClass($className);
			$classReflection = $this->getClassFromReflection(
				$reflectionClass,
				$reflectionClass->getName(),
				$reflectionClass->isAnonymous()
			);
			$this->classReflections[$className] = $classReflection;
			if ($className !== $reflectionClass->getName()) {
				// class alias optimization
				$this->classReflections[$reflectionClass->getName()] = $classReflection;
			}
		}

		return $this->classReflections[$className];
	}

	public function getClassFromReflection(\ReflectionClass $reflectionClass, string $displayName, bool $anonymous): \PHPStan\Reflection\ClassReflection
	{
		$className = $reflectionClass->getName();
		if (!isset($this->classReflections[$className])) {
			$classReflection = new ClassReflection(
				$this,
				$this->propertiesClassReflectionExtensions,
				$this->methodsClassReflectionExtensions,
				$displayName,
				$reflectionClass,
				$anonymous
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

		spl_autoload_register($autoloader = function (string $autoloadedClassName) use ($className) {
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

	public function getFunction(\PhpParser\Node\Name $nameNode, Scope $scope = null): \PHPStan\Reflection\FunctionReflection
	{
		$functionName = $this->resolveFunctionName($nameNode, $scope);
		if ($functionName === null) {
			throw new \PHPStan\Broker\FunctionNotFoundException((string) $nameNode);
		}

		$lowerCasedFunctionName = strtolower($functionName);
		if (!isset($this->functionReflections[$lowerCasedFunctionName])) {
			$reflectionFunction = new \ReflectionFunction($lowerCasedFunctionName);
			$phpDocParameterTags = [];
			$phpDocReturnTag = null;
			if ($reflectionFunction->getFileName() !== false && $reflectionFunction->getDocComment() !== false) {
				$fileName = $reflectionFunction->getFileName();
				$docComment = $reflectionFunction->getDocComment();
				$resolvedPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc($fileName, null, $docComment);
				$phpDocParameterTags = $resolvedPhpDoc->getParamTags();
				$phpDocReturnTag = $resolvedPhpDoc->getReturnTag();
			}
			$this->functionReflections[$lowerCasedFunctionName] = $this->functionReflectionFactory->create(
				$reflectionFunction,
				array_map(function (ParamTag $paramTag): Type {
					return $paramTag->getType();
				}, $phpDocParameterTags),
				$phpDocReturnTag !== null ? $phpDocReturnTag->getType() : null
			);
		}

		return $this->functionReflections[$lowerCasedFunctionName];
	}

	public function hasFunction(\PhpParser\Node\Name $nameNode, Scope $scope = null): bool
	{
		return $this->resolveFunctionName($nameNode, $scope) !== null;
	}

	/**
	 * @param \PhpParser\Node\Name $nameNode
	 * @param \PHPStan\Analyser\Scope|null $scope
	 * @return string|null
	 */
	public function resolveFunctionName(\PhpParser\Node\Name $nameNode, Scope $scope = null)
	{
		return $this->resolveName($nameNode, function (string $name): bool {
			return function_exists($name);
		}, $scope);
	}

	public function hasConstant(\PhpParser\Node\Name $nameNode, Scope $scope = null): bool
	{
		return $this->resolveConstantName($nameNode, $scope) !== null;
	}

	/**
	 * @param \PhpParser\Node\Name $nameNode
	 * @param \PHPStan\Analyser\Scope|null $scope
	 * @return string|null
	 */
	public function resolveConstantName(\PhpParser\Node\Name $nameNode, Scope $scope = null)
	{
		return $this->resolveName($nameNode, function (string $name): bool {
			return defined($name);
		}, $scope);
	}

	/**
	 * @param \PhpParser\Node\Name $nameNode
	 * @param \Closure $existsCallback
	 * @param \PHPStan\Analyser\Scope|null $scope
	 * @return string|null
	 */
	private function resolveName(
		\PhpParser\Node\Name $nameNode,
		\Closure $existsCallback,
		Scope $scope = null
	)
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
