<?php declare(strict_types = 1);

namespace PHPStan\Testing;

use PhpParser\PrettyPrinter\Standard;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierFactory;
use PHPStan\Broker\Broker;
use PHPStan\Broker\BrokerFactory;
use PHPStan\Cache\Cache;
use PHPStan\Cache\MemoryCacheStorage;
use PHPStan\DependencyInjection\ContainerFactory;
use PHPStan\File\FileHelper;
use PHPStan\Parser\FunctionCallStatementFinder;
use PHPStan\Parser\Parser;
use PHPStan\PhpDoc\PhpDocStringResolver;
use PHPStan\PhpDoc\TypeStringResolver;
use PHPStan\Reflection\Annotations\AnnotationsMethodsClassReflectionExtension;
use PHPStan\Reflection\Annotations\AnnotationsPropertiesClassReflectionExtension;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionReflectionFactory;
use PHPStan\Reflection\Php\PhpClassReflectionExtension;
use PHPStan\Reflection\Php\PhpFunctionReflection;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Reflection\Php\PhpMethodReflectionFactory;
use PHPStan\Reflection\Php\UniversalObjectCratesClassReflectionExtension;
use PHPStan\Reflection\PhpDefect\PhpDefectClassReflectionExtension;
use PHPStan\Reflection\SignatureMap\SignatureMapProvider;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\Type;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{

	/** @var \Nette\DI\Container */
	private static $container;

	public function getContainer(): \Nette\DI\Container
	{
		if (self::$container === null) {
			$rootDir = __DIR__ . '/../..';
			$containerFactory = new ContainerFactory($rootDir);
			self::$container = $containerFactory->create($rootDir . '/tmp', [
				$containerFactory->getConfigDirectory() . '/config.level7.neon',
			]);
		}

		return self::$container;
	}

	public function getParser(): \PHPStan\Parser\Parser
	{
		/** @var \PHPStan\Parser\Parser $parser */
		$parser = $this->getContainer()->getService('directParser');
		return $parser;
	}

	/**
	 * @param \PHPStan\Type\DynamicMethodReturnTypeExtension[] $dynamicMethodReturnTypeExtensions
	 * @param \PHPStan\Type\DynamicStaticMethodReturnTypeExtension[] $dynamicStaticMethodReturnTypeExtensions
	 * @return \PHPStan\Broker\Broker
	 */
	public function createBroker(
		array $dynamicMethodReturnTypeExtensions = [],
		array $dynamicStaticMethodReturnTypeExtensions = []
	): Broker
	{
		$functionCallStatementFinder = new FunctionCallStatementFinder();
		$parser = $this->getParser();
		$cache = new Cache(new MemoryCacheStorage());
		$methodReflectionFactory = new class($parser, $functionCallStatementFinder, $cache) implements PhpMethodReflectionFactory {

			/** @var \PHPStan\Parser\Parser */
			private $parser;

			/** @var \PHPStan\Parser\FunctionCallStatementFinder */
			private $functionCallStatementFinder;

			/** @var \PHPStan\Cache\Cache */
			private $cache;

			/** @var \PHPStan\Broker\Broker */
			public $broker;

			public function __construct(
				Parser $parser,
				FunctionCallStatementFinder $functionCallStatementFinder,
				Cache $cache
			)
			{
				$this->parser = $parser;
				$this->functionCallStatementFinder = $functionCallStatementFinder;
				$this->cache = $cache;
			}

			/**
			 * @param ClassReflection $declaringClass
			 * @param \ReflectionMethod $reflection
			 * @param Type[] $phpDocParameterTypes
			 * @param null|Type $phpDocReturnType
			 * @param bool $isDeprecated
			 * @return PhpMethodReflection
			 */
			public function create(
				ClassReflection $declaringClass,
				\ReflectionMethod $reflection,
				array $phpDocParameterTypes,
				?Type $phpDocReturnType,
				bool $isDeprecated
			): PhpMethodReflection
			{
				return new PhpMethodReflection(
					$declaringClass,
					$reflection,
					$this->broker,
					$this->parser,
					$this->functionCallStatementFinder,
					$this->cache,
					$phpDocParameterTypes,
					$phpDocReturnType,
					$isDeprecated
				);
			}
		};
		$phpDocStringResolver = $this->getContainer()->getByType(PhpDocStringResolver::class);
		$fileTypeMapper = new FileTypeMapper($parser, $phpDocStringResolver, $cache);
		$annotationsMethodsClassReflectionExtension = new AnnotationsMethodsClassReflectionExtension($fileTypeMapper);
		$annotationsPropertiesClassReflectionExtension = new AnnotationsPropertiesClassReflectionExtension($fileTypeMapper);
		$signatureMapProvider = $this->getContainer()->getByType(SignatureMapProvider::class);
		$phpExtension = new PhpClassReflectionExtension($methodReflectionFactory, $fileTypeMapper, $annotationsMethodsClassReflectionExtension, $annotationsPropertiesClassReflectionExtension, $signatureMapProvider);
		$functionReflectionFactory = new class($this->getParser(), $functionCallStatementFinder, $cache) implements FunctionReflectionFactory {

			/** @var \PHPStan\Parser\Parser */
			private $parser;

			/** @var \PHPStan\Parser\FunctionCallStatementFinder */
			private $functionCallStatementFinder;

			/** @var \PHPStan\Cache\Cache */
			private $cache;

			public function __construct(
				Parser $parser,
				FunctionCallStatementFinder $functionCallStatementFinder,
				Cache $cache
			)
			{
				$this->parser = $parser;
				$this->functionCallStatementFinder = $functionCallStatementFinder;
				$this->cache = $cache;
			}

			/**
			 * @param \ReflectionFunction $function
			 * @param Type[] $phpDocParameterTypes
			 * @param null|Type $phpDocReturnType
			 * @param bool $isDeprecated
			 * @return PhpFunctionReflection
			 */
			public function create(
				\ReflectionFunction $function,
				array $phpDocParameterTypes,
				?Type $phpDocReturnType,
				bool $isDeprecated
			): PhpFunctionReflection
			{
				return new PhpFunctionReflection(
					$function,
					$this->parser,
					$this->functionCallStatementFinder,
					$this->cache,
					$phpDocParameterTypes,
					$phpDocReturnType,
					$isDeprecated
				);
			}
		};

		$tagToService = function (array $tags) {
			return array_map(function (string $serviceName) {
				return $this->getContainer()->getService($serviceName);
			}, array_keys($tags));
		};

		$broker = new Broker(
			[
				$phpExtension,
				new PhpDefectClassReflectionExtension($this->getContainer()->getByType(TypeStringResolver::class)),
				new UniversalObjectCratesClassReflectionExtension([\stdClass::class]),
				$annotationsPropertiesClassReflectionExtension,
			],
			[
				$phpExtension,
				$annotationsMethodsClassReflectionExtension,
			],
			array_merge($dynamicMethodReturnTypeExtensions, $this->getDynamicMethodReturnTypeExtensions()),
			array_merge($dynamicStaticMethodReturnTypeExtensions, $this->getDynamicStaticMethodReturnTypeExtensions()),
			array_merge($tagToService($this->getContainer()->findByTag(BrokerFactory::DYNAMIC_FUNCTION_RETURN_TYPE_EXTENSION_TAG)), $this->getDynamicFunctionReturnTypeExtensions()),
			$functionReflectionFactory,
			new FileTypeMapper($this->getParser(), $phpDocStringResolver, $cache),
			$signatureMapProvider,
			$this->getContainer()->getByType(Standard::class),
			$this->getContainer()->parameters['universalObjectCratesClasses']
		);
		$methodReflectionFactory->broker = $broker;

		return $broker;
	}

	/**
	 * @return \PHPStan\Type\DynamicMethodReturnTypeExtension[]
	 */
	public function getDynamicMethodReturnTypeExtensions(): array
	{
		return [];
	}

	/**
	 * @return \PHPStan\Type\DynamicStaticMethodReturnTypeExtension[]
	 */
	public function getDynamicStaticMethodReturnTypeExtensions(): array
	{
		return [];
	}

	/**
	 * @return \PHPStan\Type\DynamicFunctionReturnTypeExtension[]
	 */
	public function getDynamicFunctionReturnTypeExtensions(): array
	{
		return [];
	}

	/**
	 * @param \PhpParser\PrettyPrinter\Standard $printer
	 * @param \PHPStan\Broker\Broker $broker
	 * @param \PHPStan\Type\MethodTypeSpecifyingExtension[] $methodTypeSpecifyingExtensions
	 * @param \PHPStan\Type\StaticMethodTypeSpecifyingExtension[] $staticMethodTypeSpecifyingExtensions
	 * @return \PHPStan\Analyser\TypeSpecifier
	 */
	public function createTypeSpecifier(
		Standard $printer,
		Broker $broker,
		array $methodTypeSpecifyingExtensions = [],
		array $staticMethodTypeSpecifyingExtensions = []
	): TypeSpecifier
	{
		$tagToService = function (array $tags) {
			return array_map(function (string $serviceName) {
				return $this->getContainer()->getService($serviceName);
			}, array_keys($tags));
		};

		return new TypeSpecifier(
			$printer,
			$broker,
			$tagToService($this->getContainer()->findByTag(TypeSpecifierFactory::FUNCTION_TYPE_SPECIFYING_EXTENSION_TAG)),
			$methodTypeSpecifyingExtensions,
			$staticMethodTypeSpecifyingExtensions
		);
	}

	public function getFileHelper(): FileHelper
	{
		return $this->getContainer()->getByType(FileHelper::class);
	}

}
