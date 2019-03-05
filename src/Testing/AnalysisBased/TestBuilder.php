<?php declare(strict_types = 1);

namespace PHPStan\Testing\AnalysisBased;

use PhpParser\PrettyPrinter\Standard;
use PHPStan\Analyser\Analyser;
use PHPStan\Analyser\Error;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\ScopeFactory;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierFactory;
use PHPStan\Broker\AnonymousClassNameHelper;
use PHPStan\Broker\Broker;
use PHPStan\Broker\BrokerFactory;
use PHPStan\Cache\Cache;
use PHPStan\Cache\MemoryCacheStorage;
use PHPStan\DependencyInjection\Container;
use PHPStan\File\FileHelper;
use PHPStan\File\FuzzyRelativePathHelper;
use PHPStan\File\RelativePathHelper;
use PHPStan\Parser\FunctionCallStatementFinder;
use PHPStan\Parser\Parser;
use PHPStan\PhpDoc\PhpDocStringResolver;
use PHPStan\PhpDoc\TypeNodeResolverExtension;
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
use PHPStan\Testing\TestCase;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\MethodTypeSpecifyingExtension;
use PHPStan\Type\StaticMethodTypeSpecifyingExtension;
use PHPStan\Type\Type;
use PHPUnit\Framework\Assert;

class TestBuilder
{

	/** @var \PHPStan\Analyser\Analyser|null */
	private $analyser;

	/** @var UtilsRule */
	private $utilsRule;

	/** @var Rule[] */
	private $rules = [];

	/** @var MethodTypeSpecifyingExtension[] */
	private $methodTypeSpecifyingExtensions = [];

	/** @var StaticMethodTypeSpecifyingExtension[] */
	private $staticMethodTypeSpecifyingExtensions = [];

	/** @var TypeNodeResolverExtension[] */
	private $typeNodeResolverExtensions = [];

	/** @var \PHPStan\Type\DynamicMethodReturnTypeExtension[] */
	private $dynamicMethodReturnTypeExtensions = [];

	/** @var \PHPStan\Type\DynamicStaticMethodReturnTypeExtension[] */
	private $dynamicStaticMethodReturnTypeExtensions = [];

	/** @var \PHPStan\Type\DynamicFunctionReturnTypeExtension[] */
	private $dynamicFunctionReturnTypeExtensions = [];

	/** @var bool */
	private $shouldPolluteScopeWithLoopInitialAssignments = false;

	/** @var bool */
	private $shouldPolluteCatchScopeWithTryAssignments = false;

	/** @var bool */
	private $shouldPolluteScopeWithAlwaysIterableForeach = true;

	/** @var Cache */
	private $cache;

	public function __construct(Cache $cache)
	{
		$this->utilsRule = new UtilsRule();
		$this->cache = $cache;
	}

	/**
	 * @param Rule[] $rules
	 */
	public function setRules(array $rules): self
	{
		$this->rules = $rules;

		return $this;
	}

	/**
	 * @param MethodTypeSpecifyingExtension[] $methodTypeSpecifyingExtensions
	 */
	public function setMethodTypeSpecifyingExtensions(array $methodTypeSpecifyingExtensions): self
	{
		$this->methodTypeSpecifyingExtensions = $methodTypeSpecifyingExtensions;

		return $this;
	}

	/**
	 * @param StaticMethodTypeSpecifyingExtension[] $staticMethodTypeSpecifyingExtensions
	 */
	public function setStaticMethodTypeSpecifyingExtensions(array $staticMethodTypeSpecifyingExtensions): self
	{
		$this->staticMethodTypeSpecifyingExtensions = $staticMethodTypeSpecifyingExtensions;

		return $this;
	}

	/**
	 * @param TypeNodeResolverExtension[] $typeNodeResolverExtensions
	 */
	public function setTypeNodeResolverExtensions(array $typeNodeResolverExtensions): self
	{
		$this->typeNodeResolverExtensions = $typeNodeResolverExtensions;

		return $this;
	}

	/**
	 * @param \PHPStan\Type\DynamicMethodReturnTypeExtension[] $dynamicMethodReturnTypeExtensions
	 */
	public function setDynamicMethodReturnTypeExtensions(array $dynamicMethodReturnTypeExtensions): self
	{
		$this->dynamicMethodReturnTypeExtensions = $dynamicMethodReturnTypeExtensions;

		return $this;
	}

	/**
	 * @param \PHPStan\Type\DynamicStaticMethodReturnTypeExtension[] $dynamicStaticMethodReturnTypeExtensions
	 */
	public function setDynamicStaticMethodReturnTypeExtensions(array $dynamicStaticMethodReturnTypeExtensions): self
	{
		$this->dynamicStaticMethodReturnTypeExtensions = $dynamicStaticMethodReturnTypeExtensions;

		return $this;
	}

	/**
	 * @param \PHPStan\Type\DynamicFunctionReturnTypeExtension[] $dynamicFunctionReturnTypeExtensions
	 */
	public function setDynamicFunctionReturnTypeExtensions(array $dynamicFunctionReturnTypeExtensions): self
	{
		$this->dynamicFunctionReturnTypeExtensions = $dynamicFunctionReturnTypeExtensions;

		return $this;
	}

	public function setShouldPolluteScopeWithLoopInitialAssignments(bool $shouldPolluteScopeWithLoopInitialAssignments): self
	{
		$this->shouldPolluteScopeWithLoopInitialAssignments = $shouldPolluteScopeWithLoopInitialAssignments;

		return $this;
	}

	public function setShouldPolluteCatchScopeWithTryAssignments(bool $shouldPolluteCatchScopeWithTryAssignments): self
	{
		$this->shouldPolluteCatchScopeWithTryAssignments = $shouldPolluteCatchScopeWithTryAssignments;

		return $this;
	}

	public function setShouldPolluteScopeWithAlwaysIterableForeach(bool $shouldPolluteScopeWithAlwaysIterableForeach): self
	{
		$this->shouldPolluteScopeWithAlwaysIterableForeach = $shouldPolluteScopeWithAlwaysIterableForeach;

		return $this;
	}

	public function checkFile(string $file): self
	{
		$file = $this->getFileHelper()->normalizePath($file);
		$actualErrors = $this->getAnalyser()->analyse([$file], false);

		$strictlyTypedSprintf = static function (int $line, string $message): string {
			return sprintf('%02d: %s', $line, $message);
		};

		$expectedErrors = $this->utilsRule->getExpectedErrors();
		$expectedErrors = array_map(
			static function (array $error) use ($strictlyTypedSprintf): string {
				if (!isset($error[0])) {
					throw new \InvalidArgumentException('Missing expected error message.');
				}
				if (!isset($error[1])) {
					throw new \InvalidArgumentException('Missing expected file line.');
				}
				return $strictlyTypedSprintf($error[1], $error[0]);
			},
			$expectedErrors
		);

		$actualErrors = array_map(
			static function (Error $error) use ($strictlyTypedSprintf): string {
				return $strictlyTypedSprintf((int) $error->getLine(), $error->getMessage());
			},
			$actualErrors
		);

		$errorMessages = [];
		foreach (array_diff($expectedErrors, $actualErrors) as $error) {
			$errorParts = explode(':', $error, 2);
			$errorMessages[] = $this->prepareCodeSnippet('Expected error did not occur: ' . ltrim($errorParts[1]), $file, (int) $errorParts[0]);
		}

		foreach (array_diff($actualErrors, $expectedErrors) as $error) {
			$errorParts = explode(':', $error, 2);
			$errorMessages[] = $this->prepareCodeSnippet('Unexpected error: ' . ltrim($errorParts[1]), $file, (int) $errorParts[0]);
		}

		Assert::assertCount(0, $errorMessages, implode("\n", $errorMessages));

		return $this;
	}

	private function getParser(): \PHPStan\Parser\Parser
	{
		/** @var \PHPStan\Parser\Parser $parser */
		$parser = TestCase::getContainer()->getService('directParser');
		return $parser;
	}

	private function createBroker(): Broker
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
			 * @param ClassReflection|null $declaringTrait
			 * @param \PHPStan\Reflection\Php\BuiltinMethodReflection $reflection
			 * @param Type[] $phpDocParameterTypes
			 * @param Type|null $phpDocReturnType
			 * @param Type|null $phpDocThrowType
			 * @param bool $isDeprecated
			 * @param bool $isInternal
			 * @param bool $isFinal
			 * @return PhpMethodReflection
			 */
			public function create(
				ClassReflection $declaringClass,
				?ClassReflection $declaringTrait,
				\PHPStan\Reflection\Php\BuiltinMethodReflection $reflection,
				array $phpDocParameterTypes,
				?Type $phpDocReturnType,
				?Type $phpDocThrowType,
				bool $isDeprecated,
				bool $isInternal,
				bool $isFinal
			): PhpMethodReflection
			{
				return new PhpMethodReflection(
					$declaringClass,
					$declaringTrait,
					$reflection,
					$this->broker,
					$this->parser,
					$this->functionCallStatementFinder,
					$this->cache,
					$phpDocParameterTypes,
					$phpDocReturnType,
					$phpDocThrowType,
					$isDeprecated,
					$isInternal,
					$isFinal
				);
			}

		};
		$phpDocStringResolver = TestCase::getContainer()->getByType(PhpDocStringResolver::class);
		$currentWorkingDirectory = $this->getCurrentWorkingDirectory();
		$relativePathHelper = new FuzzyRelativePathHelper($this->getCurrentWorkingDirectory(), DIRECTORY_SEPARATOR, []);
		$fileTypeMapper = new FileTypeMapper($parser, $phpDocStringResolver, $cache, new AnonymousClassNameHelper(new FileHelper($currentWorkingDirectory), $relativePathHelper), TestCase::getContainer()->getByType(\PHPStan\PhpDoc\TypeNodeResolver::class));
		$annotationsMethodsClassReflectionExtension = new AnnotationsMethodsClassReflectionExtension($fileTypeMapper);
		$annotationsPropertiesClassReflectionExtension = new AnnotationsPropertiesClassReflectionExtension($fileTypeMapper);
		$signatureMapProvider = TestCase::getContainer()->getByType(SignatureMapProvider::class);
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
			 * @param Type|null $phpDocReturnType
			 * @param Type|null $phpDocThrowType
			 * @param bool $isDeprecated
			 * @param bool $isInternal
			 * @param bool $isFinal
			 * @param string|false $filename
			 * @return PhpFunctionReflection
			 */
			public function create(
				\ReflectionFunction $function,
				array $phpDocParameterTypes,
				?Type $phpDocReturnType,
				?Type $phpDocThrowType,
				bool $isDeprecated,
				bool $isInternal,
				bool $isFinal,
				$filename
			): PhpFunctionReflection
			{
				return new PhpFunctionReflection(
					$function,
					$this->parser,
					$this->functionCallStatementFinder,
					$this->cache,
					$phpDocParameterTypes,
					$phpDocReturnType,
					$phpDocThrowType,
					$isDeprecated,
					$isInternal,
					$isFinal,
					$filename
				);
			}

		};

		$tagToService = static function (array $tags) {
			return array_map(static function (string $serviceName) {
				return TestCase::getContainer()->getService($serviceName);
			}, array_keys($tags));
		};

		$currentWorkingDirectory = $this->getCurrentWorkingDirectory();
		$anonymousClassNameHelper = new AnonymousClassNameHelper(new FileHelper($currentWorkingDirectory), $relativePathHelper);
		$broker = new Broker(
			[
				$phpExtension,
				new PhpDefectClassReflectionExtension(TestCase::getContainer()->getByType(TypeStringResolver::class), $annotationsPropertiesClassReflectionExtension),
				new UniversalObjectCratesClassReflectionExtension([\stdClass::class]),
				$annotationsPropertiesClassReflectionExtension,
			],
			[
				$phpExtension,
				$annotationsMethodsClassReflectionExtension,
			],
			$this->dynamicMethodReturnTypeExtensions,
			$this->dynamicStaticMethodReturnTypeExtensions,
			array_merge(
				$tagToService(TestCase::getContainer()->findByTag(BrokerFactory::DYNAMIC_FUNCTION_RETURN_TYPE_EXTENSION_TAG)),
				$this->dynamicFunctionReturnTypeExtensions
			),
			$functionReflectionFactory,
			new FileTypeMapper($this->getParser(), $phpDocStringResolver, $cache, $anonymousClassNameHelper, TestCase::getContainer()->getByType(\PHPStan\PhpDoc\TypeNodeResolver::class)),
			$signatureMapProvider,
			TestCase::getContainer()->getByType(Standard::class),
			$anonymousClassNameHelper,
			TestCase::getContainer()->getByType(Parser::class),
			$relativePathHelper,
			TestCase::getContainer()->parameters['universalObjectCratesClasses']
		);
		$methodReflectionFactory->broker = $broker;

		return $broker;
	}

	private function prepareCodeSnippet(string $error, string $file, int $line): string
	{
		$fileData = file_get_contents($file);
		if ($fileData === false) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$fileContent = explode("\n", $fileData);
		$snippetStart = max(0, $line - 3);
		$fileContent = array_slice($fileContent, $snippetStart, min(count($fileContent) - $snippetStart, 4));
		$snippetLine = $line - $snippetStart - 1;

		$fileContent[$snippetLine] = "\033[1m" . $fileContent[$snippetLine] . "\033[0m";
		foreach ($fileContent as $key => $row) {
			$fileContent[$key] = sprintf('| %4d | %s', $snippetStart + $key + 1, $row);
		}

		return sprintf("%s\nFile %s:%d\n\n| …\n%s\n| …\n\n", $error, $file, $line, implode("\n", $fileContent));
	}

	private function getAnalyser(): Analyser
	{
		if ($this->analyser === null) {
			$registry = new Registry(array_merge($this->rules, [$this->utilsRule]));
			$broker = $this->createBroker();
			$printer = new \PhpParser\PrettyPrinter\Standard();
			$fileHelper = $this->getFileHelper();
			$typeSpecifier = $this->createTypeSpecifier(
				$printer,
				$broker,
				$this->methodTypeSpecifyingExtensions,
				$this->staticMethodTypeSpecifyingExtensions
			);
			$currentWorkingDirectory = $this->getCurrentWorkingDirectory();
			$this->analyser = new Analyser(
				$this->createScopeFactory($broker, $typeSpecifier),
				$this->getParser(),
				$registry,
				new NodeScopeResolver(
					$broker,
					$this->getParser(),
					new FileTypeMapper($this->getParser(), TestCase::getContainer()->getByType(PhpDocStringResolver::class), $this->cache, new AnonymousClassNameHelper(new FileHelper($currentWorkingDirectory), new FuzzyRelativePathHelper($currentWorkingDirectory, DIRECTORY_SEPARATOR, [])), new \PHPStan\PhpDoc\TypeNodeResolver($this->typeNodeResolverExtensions)),
					$fileHelper,
					$typeSpecifier,
					$this->shouldPolluteScopeWithLoopInitialAssignments,
					$this->shouldPolluteCatchScopeWithTryAssignments,
					$this->shouldPolluteScopeWithAlwaysIterableForeach,
					[]
				),
				$fileHelper,
				[],
				true,
				50
			);
		}

		return $this->analyser;
	}

	private function getFileHelper(): FileHelper
	{
		return TestCase::getContainer()->getByType(FileHelper::class);
	}

	/**
	 * @param \PhpParser\PrettyPrinter\Standard $printer
	 * @param \PHPStan\Broker\Broker $broker
	 * @param \PHPStan\Type\MethodTypeSpecifyingExtension[] $methodTypeSpecifyingExtensions
	 * @param \PHPStan\Type\StaticMethodTypeSpecifyingExtension[] $staticMethodTypeSpecifyingExtensions
	 * @return \PHPStan\Analyser\TypeSpecifier
	 */
	private function createTypeSpecifier(
		Standard $printer,
		Broker $broker,
		array $methodTypeSpecifyingExtensions = [],
		array $staticMethodTypeSpecifyingExtensions = []
	): TypeSpecifier
	{
		$tagToService = static function (array $tags) {
			return array_map(static function (string $serviceName) {
				return TestCase::getContainer()->getService($serviceName);
			}, array_keys($tags));
		};

		return new TypeSpecifier(
			$printer,
			$broker,
			$tagToService(TestCase::getContainer()->findByTag(TypeSpecifierFactory::FUNCTION_TYPE_SPECIFYING_EXTENSION_TAG)),
			$methodTypeSpecifyingExtensions,
			$staticMethodTypeSpecifyingExtensions
		);
	}

	/**
	 * @param Broker $broker
	 * @param TypeSpecifier $typeSpecifier
	 * @param string[] $dynamicConstantNames
	 *
	 * @return ScopeFactory
	 */
	private function createScopeFactory(Broker $broker, TypeSpecifier $typeSpecifier, array $dynamicConstantNames = []): ScopeFactory
	{
		$container = TestCase::getContainer();

		if (count($dynamicConstantNames) > 0) {
			$container->parameters['dynamicConstantNames'] = array_merge($container->parameters['dynamicConstantNames'], $dynamicConstantNames);
		}

		return new ScopeFactory(
			Scope::class,
			$broker,
			new \PhpParser\PrettyPrinter\Standard(),
			$typeSpecifier,
			$container->getByType(Container::class)
		);
	}

	private function getCurrentWorkingDirectory(): string
	{
		return $this->getFileHelper()->normalizePath(__DIR__ . '/../../..');
	}

}
