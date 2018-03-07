<?php declare(strict_types = 1);

namespace PHPStan\Testing;

use Nette\DI\Container;
use PhpParser\PrettyPrinter\Standard;
use PHPStan\Analyser\Analyser;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Broker\Broker;
use PHPStan\Broker\BrokerFactory;
use PHPStan\Cache\Cache;
use PHPStan\Cache\MemoryCacheStorage;
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
use PHPStan\Rules\Registry;
use PHPStan\Rules\Rule;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\Type;

final class TestingKit
{

	/** @var Container  */
	private $container;

	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	public function getAnalyser(
		Rule $rule,
		Cache $cache,
		bool $shouldPolluteScopeWithLoopInitialAssignments = false,
		bool $shouldPolluteCatchScopeWithTryAssignments = false
	): Analyser
	{
		$registry = new Registry([$rule]);
		$broker = $this->createBroker();
		$printer = new Standard();
		$fileHelper = $this->getFileHelper();
		$typeSpecifier = new TypeSpecifier($printer);

		return new Analyser(
			$broker,
			$this->getParser(),
			$registry,
			new NodeScopeResolver(
				$broker,
				$this->getParser(),
				$printer,
				new FileTypeMapper(
					$this->getParser(),
					$this->getPhpDocStringResolver(),
					$cache
				),
				$fileHelper,
				$shouldPolluteScopeWithLoopInitialAssignments,
				$shouldPolluteCatchScopeWithTryAssignments,
				[]
			),
			$printer,
			$typeSpecifier,
			$fileHelper,
			[],
			null,
			true,
			50
		);
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

		$phpDocStringResolver = $this->getPhpDocStringResolver();
		$fileTypeMapper = new FileTypeMapper($parser, $phpDocStringResolver, $cache);
		$annotationsPropertiesClassReflectionExtension = new AnnotationsPropertiesClassReflectionExtension($fileTypeMapper);
		$phpExtension = new PhpClassReflectionExtension(
			$methodReflectionFactory,
			$fileTypeMapper,
			new AnnotationsMethodsClassReflectionExtension($fileTypeMapper),
			$annotationsPropertiesClassReflectionExtension,
			$this->getSignatureMapProvider()
		);

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
			): PhpFunctionReflection
			{
				return new PhpFunctionReflection(
					$function,
					$this->parser,
					$this->functionCallStatementFinder,
					$this->cache,
					$phpDocParameterTypes,
					$phpDocReturnType
				);
			}
		};

		$tagToService = function (array $tags) {
			return array_map(function (string $serviceName) {
				return $this->container->getService($serviceName);
			}, array_keys($tags));
		};

		$broker = new Broker(
			[
				$phpExtension,
				$annotationsPropertiesClassReflectionExtension,
				new UniversalObjectCratesClassReflectionExtension([\stdClass::class]),
				new PhpDefectClassReflectionExtension($this->getTypeStringResolver()),
			],
			[$phpExtension],
			$dynamicMethodReturnTypeExtensions,
			$dynamicStaticMethodReturnTypeExtensions,
			$tagToService($this->container->findByTag(BrokerFactory::DYNAMIC_FUNCTION_RETURN_TYPE_EXTENSION_TAG)),
			$functionReflectionFactory,
			new FileTypeMapper($this->getParser(), $phpDocStringResolver, $cache),
			$this->getSignatureMapProvider()
		);
		$methodReflectionFactory->broker = $broker;

		return $broker;
	}

	private function getPhpDocStringResolver(): PhpDocStringResolver
	{
		/** @var PhpDocStringResolver $resolver */
		$resolver = $this->container->getByType(PhpDocStringResolver::class);
		return $resolver;
	}

	public function getFileHelper(): FileHelper
	{
		/** @var FileHelper $fileHelper */
		$fileHelper = $this->container->getByType(FileHelper::class);
		return $fileHelper;
	}

	public function getParser(): Parser
	{
		/** @var Parser $parser */
		$parser = $this->container->getService('directParser');
		return $parser;
	}

	private function getTypeStringResolver(): TypeStringResolver
	{
		/** @var TypeStringResolver $resolver */
		$resolver = $this->container->getByType(TypeStringResolver::class);
		return $resolver;
	}

	private function getSignatureMapProvider(): SignatureMapProvider
	{
		/** @var SignatureMapProvider $provider */
		$provider = $this->container->getByType(SignatureMapProvider::class);
		return $provider;
	}

}
