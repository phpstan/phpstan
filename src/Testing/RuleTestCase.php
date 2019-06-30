<?php declare(strict_types = 1);

namespace PHPStan\Testing;

use PHPStan\Analyser\Analyser;
use PHPStan\Analyser\Error;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Broker\AnonymousClassNameHelper;
use PHPStan\Cache\Cache;
use PHPStan\File\FileHelper;
use PHPStan\File\FuzzyRelativePathHelper;
use PHPStan\PhpDoc\PhpDocStringResolver;
use PHPStan\Rules\Registry;
use PHPStan\Rules\Rule;
use PHPStan\Type\FileTypeMapper;

abstract class RuleTestCase extends \PHPStan\Testing\TestCase
{

	/** @var \PHPStan\Analyser\Analyser|null */
	private $analyser;

	abstract protected function getRule(): Rule;

	protected function getTypeSpecifier(): TypeSpecifier
	{
		return $this->createTypeSpecifier(
			new \PhpParser\PrettyPrinter\Standard(),
			$this->createBroker(),
			$this->getMethodTypeSpecifyingExtensions(),
			$this->getStaticMethodTypeSpecifyingExtensions()
		);
	}

	private function getAnalyser(): Analyser
	{
		if ($this->analyser === null) {
			$registry = new Registry([
				$this->getRule(),
			]);

			$broker = $this->createBroker();
			$printer = new \PhpParser\PrettyPrinter\Standard();
			$fileHelper = $this->getFileHelper();
			$typeSpecifier = $this->createTypeSpecifier(
				$printer,
				$broker,
				$this->getMethodTypeSpecifyingExtensions(),
				$this->getStaticMethodTypeSpecifyingExtensions()
			);
			$currentWorkingDirectory = $this->getCurrentWorkingDirectory();
			$this->analyser = new Analyser(
				$this->createScopeFactory($broker, $typeSpecifier),
				$this->getParser(),
				$registry,
				new NodeScopeResolver(
					$broker,
					$this->getParser(),
					new FileTypeMapper($this->getParser(), self::getContainer()->getByType(PhpDocStringResolver::class), $this->createMock(Cache::class), new AnonymousClassNameHelper(new FileHelper($currentWorkingDirectory), new FuzzyRelativePathHelper($currentWorkingDirectory, DIRECTORY_SEPARATOR, [])), new \PHPStan\PhpDoc\TypeNodeResolver($this->getTypeNodeResolverExtensions())),
					$fileHelper,
					$typeSpecifier,
					$this->shouldPolluteScopeWithLoopInitialAssignments(),
					$this->shouldPolluteCatchScopeWithTryAssignments(),
					$this->shouldPolluteScopeWithAlwaysIterableForeach(),
					[],
					false
				),
				$fileHelper,
				[],
				true,
				50
			);
		}

		return $this->analyser;
	}

	/**
	 * @return \PHPStan\Type\MethodTypeSpecifyingExtension[]
	 */
	protected function getMethodTypeSpecifyingExtensions(): array
	{
		return [];
	}

	/**
	 * @return \PHPStan\Type\StaticMethodTypeSpecifyingExtension[]
	 */
	protected function getStaticMethodTypeSpecifyingExtensions(): array
	{
		return [];
	}

	/**
	 * @return \PHPStan\PhpDoc\TypeNodeResolverExtension[]
	 */
	protected function getTypeNodeResolverExtensions(): array
	{
		return [];
	}

	/**
	 * @param string[] $files
	 * @param mixed[] $expectedErrors
	 */
	public function analyse(array $files, array $expectedErrors): void
	{
		$files = array_map([$this->getFileHelper(), 'normalizePath'], $files);
		$actualErrors = $this->getAnalyser()->analyse($files, false);

		$strictlyTypedSprintf = static function (int $line, string $message): string {
			return sprintf('%02d: %s', $line, $message);
		};

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
			static function (Error $error): string {
				return sprintf('%02d: %s', $error->getLine(), $error->getMessage());
			},
			$actualErrors
		);

		$this->assertSame(implode("\n", $expectedErrors), implode("\n", $actualErrors));
	}

	protected function shouldPolluteScopeWithLoopInitialAssignments(): bool
	{
		return false;
	}

	protected function shouldPolluteCatchScopeWithTryAssignments(): bool
	{
		return false;
	}

	protected function shouldPolluteScopeWithAlwaysIterableForeach(): bool
	{
		return true;
	}

}
