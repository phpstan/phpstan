<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PHPStan\Analyser\Analyser;
use PHPStan\Analyser\Error;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Cache\Cache;
use PHPStan\File\FileExcluder;
use PHPStan\Type\FileTypeMapper;

abstract class AbstractRuleTest extends \PHPStan\TestCase
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
			$fileExcluder = new FileExcluder($fileHelper, []);
			$typeSpecifier = new TypeSpecifier($printer);
			$this->analyser = new Analyser(
				$broker,
				$this->getParser(),
				$registry,
				new NodeScopeResolver(
					$broker,
					$this->getParser(),
					$printer,
					new FileTypeMapper($this->getParser(), $this->createMock(Cache::class)),
					$fileExcluder,
					new \PhpParser\BuilderFactory(),
					$fileHelper,
					false,
					false,
					[]
				),
				$printer,
				$typeSpecifier,
				$fileExcluder,
				$fileHelper,
				[],
				null,
				true
			);
		}

		return $this->analyser;
	}

	public function analyse(array $files, array $expectedErrors)
	{
		$files = array_map([$this->getFileHelper(), 'normalizePath'], $files);
		$actualErrors = $this->getAnalyser()->analyse($files, false);
		$this->assertInternalType('array', $actualErrors);

		$expectedErrors = array_map(
			function (array $error): string {
				return sprintf('%02d: %s', $error[1], $error[0]);
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

}
