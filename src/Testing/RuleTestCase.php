<?php declare(strict_types = 1);

namespace PHPStan\Testing;

use PHPStan\Analyser\Analyser;
use PHPStan\Analyser\Error;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Cache\Cache;
use PHPStan\PhpDoc\PhpDocStringResolver;
use PHPStan\Rules\Registry;
use PHPStan\Rules\Rule;
use PHPStan\Type\FileTypeMapper;

abstract class RuleTestCase extends \PHPStan\Testing\TestCase
{

	/** @var \PHPStan\Analyser\Analyser */
	private $analyser;

	abstract protected function getRule(): Rule;

	private function getAnalyser(): Analyser
	{
		if ($this->analyser === null) {
			$registry = new Registry([
				$this->getRule(),
			]);

			$broker = $this->createBroker();
			$printer = new \PhpParser\PrettyPrinter\Standard();
			$fileHelper = $this->getFileHelper();
			$typeSpecifier = new TypeSpecifier($printer);
			$this->analyser = new Analyser(
				$broker,
				$this->getParser(),
				$registry,
				new NodeScopeResolver(
					$broker,
					$this->getParser(),
					$printer,
					new FileTypeMapper($this->getParser(), $this->getContainer()->getByType(PhpDocStringResolver::class), $this->createMock(Cache::class)),
					$fileHelper,
					$this->shouldPolluteScopeWithLoopInitialAssignments(),
					$this->shouldPolluteCatchScopeWithTryAssignments(),
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

		return $this->analyser;
	}

	public function analyse(array $files, array $expectedErrors)
	{
		$files = array_map([$this->getFileHelper(), 'normalizePath'], $files);
		$actualErrors = $this->getAnalyser()->analyse($files, false);
		$this->assertInternalType('array', $actualErrors);

		$strictlyTypedSprintf = function (int $line, string $message): string {
			return sprintf('%02d: %s', $line, $message);
		};

		$expectedErrors = array_map(
			function (array $error) use ($strictlyTypedSprintf): string {
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
			function (Error $error): string {
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

}
