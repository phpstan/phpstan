<?php declare(strict_types = 1);

namespace PHPStan\Testing;

use PHPStan\Broker\Broker;
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
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\FunctionReflectionFactory;
use PHPStan\Reflection\Php\PhpClassReflectionExtension;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Reflection\Php\PhpMethodReflectionFactory;
use PHPStan\Reflection\Php\UniversalObjectCratesClassReflectionExtension;
use PHPStan\Reflection\PhpDefect\PhpDefectClassReflectionExtension;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\Php\AllArgumentBasedFunctionReturnTypeExtension;
use PHPStan\Type\Php\ArgumentBasedArrayFunctionReturnTypeExtension;
use PHPStan\Type\Php\ArgumentBasedFunctionReturnTypeExtension;
use PHPStan\Type\Php\ArrayFilterFunctionReturnTypeReturnTypeExtension;
use PHPStan\Type\Php\CallbackBasedArrayFunctionReturnTypeExtension;
use PHPStan\Type\Php\CallbackBasedFunctionReturnTypeExtension;
use PHPStan\Type\Type;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{

	/**
	 * @var \Nette\DI\Container
	 */
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

			public function create(
				ClassReflection $declaringClass,
				\ReflectionMethod $reflection,
				array $phpDocParameterTypes,
				Type $phpDocReturnType = null
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
					$phpDocReturnType
				);
			}
		};
		$phpDocStringResolver = $this->getContainer()->getByType(PhpDocStringResolver::class);
		$fileTypeMapper = new FileTypeMapper($parser, $phpDocStringResolver, $cache);
		$annotationsPropertiesClassReflectionExtension = new AnnotationsPropertiesClassReflectionExtension($fileTypeMapper);
		$phpExtension = new PhpClassReflectionExtension($methodReflectionFactory, $fileTypeMapper, new AnnotationsMethodsClassReflectionExtension($fileTypeMapper), $annotationsPropertiesClassReflectionExtension);
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

			public function create(
				\ReflectionFunction $function,
				array $phpDocParameterTypes,
				Type $phpDocReturnType = null
			): FunctionReflection
			{
				return new FunctionReflection(
					$function,
					$this->parser,
					$this->functionCallStatementFinder,
					$this->cache,
					$phpDocParameterTypes,
					$phpDocReturnType
				);
			}
		};
		$broker = new Broker(
			[
				$phpExtension,
				$annotationsPropertiesClassReflectionExtension,
				new UniversalObjectCratesClassReflectionExtension([\stdClass::class]),
				new PhpDefectClassReflectionExtension($this->getContainer()->getByType(TypeStringResolver::class)),
			],
			[$phpExtension],
			$dynamicMethodReturnTypeExtensions,
			$dynamicStaticMethodReturnTypeExtensions,
			[
				new AllArgumentBasedFunctionReturnTypeExtension(),
				new ArgumentBasedArrayFunctionReturnTypeExtension(),
				new ArgumentBasedFunctionReturnTypeExtension(),
				new ArrayFilterFunctionReturnTypeReturnTypeExtension(),
				new CallbackBasedArrayFunctionReturnTypeExtension(),
				new CallbackBasedFunctionReturnTypeExtension(),
			],
			$functionReflectionFactory,
			new FileTypeMapper($this->getParser(), $phpDocStringResolver, $cache)
		);
		$methodReflectionFactory->broker = $broker;

		return $broker;
	}

	public function getFileHelper(): FileHelper
	{
		return $this->getContainer()->getByType(FileHelper::class);
	}

}
