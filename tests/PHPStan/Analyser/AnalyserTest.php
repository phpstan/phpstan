<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PHPStan\Broker\AnonymousClassNameHelper;
use PHPStan\Cache\Cache;
use PHPStan\File\FileHelper;
use PHPStan\File\RelativePathHelper;
use PHPStan\Parser\DirectParser;
use PHPStan\PhpDoc\PhpDocStringResolver;
use PHPStan\Rules\AlwaysFailRule;
use PHPStan\Rules\Registry;
use PHPStan\Type\FileTypeMapper;

class AnalyserTest extends \PHPStan\Testing\TestCase
{

	public function testReturnErrorIfIgnoredMessagesDoesNotOccur(): void
	{
		$result = $this->runAnalyser(['#Unknown error#'], true, __DIR__ . '/data/empty/empty.php', false);
		$this->assertSame([
			'Ignored error pattern #Unknown error# was not matched in reported errors.',
		], $result);
	}

	public function testDoNotReturnErrorIfIgnoredMessagesDoesNotOccurWithReportUnmatchedIgnoredErrorsOff(): void
	{
		$result = $this->runAnalyser(['#Unknown error#'], false, __DIR__ . '/data/empty/empty.php', false);
		$this->assertEmpty($result);
	}

	public function testDoNotReturnErrorIfIgnoredMessagesDoNotOccurWhileAnalysingIndividualFiles(): void
	{
		$result = $this->runAnalyser(['#Unknown error#'], true, __DIR__ . '/data/empty/empty.php', true);
		$this->assertEmpty($result);
	}

	public function testReportInvalidIgnorePatternEarly(): void
	{
		$result = $this->runAnalyser(['#Regexp syntax error'], true, __DIR__ . '/data/parse-error.php', false);
		$this->assertSame([
			"No ending delimiter '#' found in pattern: #Regexp syntax error",
		], $result);
	}

	public function testFileWithAnIgnoredError(): void
	{
		$result = $this->runAnalyser(['#Fail\.#'], true, __DIR__ . '/data/bootstrap-error.php', false);
		$this->assertEmpty($result);
	}

	public function testIgnoringBrokenConfigurationDoesNotWork(): void
	{
		$result = $this->runAnalyser(['#was not found while trying to analyse it#'], true, __DIR__ . '/../../notAutoloaded/Baz.php', false);
		$this->assertCount(2, $result);
		assert($result[0] instanceof Error);
		$this->assertSame('Class PHPStan\Tests\Baz was not found while trying to analyse it - autoloading is probably not configured properly.', $result[0]->getMessage());
		$this->assertSame('Error message "Class PHPStan\Tests\Baz was not found while trying to analyse it - autoloading is probably not configured properly." cannot be ignored, use excludes_analyse instead.', $result[1]);
	}

	public function testIgnoreErrorByPath(): void
	{
		$ignoreErrors = [
			[
				'message' => '#Fail\.#',
				'path' => __DIR__ . '/data/bootstrap-error.php',
			],
		];
		$result = $this->runAnalyser($ignoreErrors, true, __DIR__ . '/data/bootstrap-error.php', false);
		$this->assertCount(0, $result);
	}

	public function testIgnoreErrorNotFoundInPath(): void
	{
		$ignoreErrors = [
			[
				'message' => '#Fail\.#',
				'path' => __DIR__ . '/data/not-existent-path.php',
			],
		];
		$result = $this->runAnalyser($ignoreErrors, true, __DIR__ . '/data/empty/empty.php', false);
		$this->assertCount(1, $result);
		$this->assertSame('Ignored error pattern #Fail\.# in path ' . __DIR__ . '/data/not-existent-path.php was not matched in reported errors.', $result[0]);
	}

	public function testIgnoredErrorMissingMessage(): void
	{
		$ignoreErrors = [
			[
				'path' => __DIR__ . '/data/empty/empty.php',
			],
		];

		$expectedPath = __DIR__;

		if (strtoupper((string) substr(PHP_OS, 0, 3)) === 'WIN') {
			$expectedPath = str_replace('\\', '\\\\', $expectedPath);
		}

		$result = $this->runAnalyser($ignoreErrors, true, __DIR__ . '/data/empty/empty.php', false);
		$this->assertCount(1, $result);
		$this->assertSame('Ignored error {"path":"' . $expectedPath . '/data/empty/empty.php"} is missing a message.', $result[0]);
	}

	public function testIgnoredErrorMissingPath(): void
	{
		$ignoreErrors = [
			[
				'message' => '#Fail\.#',
			],
		];
		$result = $this->runAnalyser($ignoreErrors, true, __DIR__ . '/data/empty/empty.php', false);
		$this->assertCount(1, $result);
		$this->assertSame('Ignored error {"message":"#Fail\\\\.#"} is missing a path.', $result[0]);
	}

	public function testIgnoredErrorMessageStillValidatedIfMissingAPath(): void
	{
		$ignoreErrors = [
			[
				'message' => '#Fail\.',
			],
		];
		$result = $this->runAnalyser($ignoreErrors, true, __DIR__ . '/data/empty/empty.php', false);
		$this->assertCount(2, $result);
		$this->assertSame('Ignored error {"message":"#Fail\\\\."} is missing a path.', $result[0]);
		$this->assertSame('No ending delimiter \'#\' found in pattern: #Fail\.', $result[1]);
	}

	public function testReportMultipleParserErrorsAtOnce(): void
	{
		$result = $this->runAnalyser([], false, __DIR__ . '/data/multipleParseErrors.php', false);
		$this->assertCount(2, $result);

		/** @var Error $errorOne */
		$errorOne = $result[0];
		$this->assertSame('Syntax error, unexpected T_IS_EQUAL, expecting T_VARIABLE on line 3', $errorOne->getMessage());
		$this->assertSame(3, $errorOne->getLine());

		/** @var Error $errorTwo */
		$errorTwo = $result[1];
		$this->assertSame('Syntax error, unexpected EOF on line 10', $errorTwo->getMessage());
		$this->assertSame(10, $errorTwo->getLine());
	}

	/**
	 * @param string[]|array<array<string, string>> $ignoreErrors
	 * @param bool $reportUnmatchedIgnoredErrors
	 * @param string $filePath
	 * @param bool $onlyFiles
	 * @return string[]|\PHPStan\Analyser\Error[]
	 */
	private function runAnalyser(
		array $ignoreErrors,
		bool $reportUnmatchedIgnoredErrors,
		string $filePath,
		bool $onlyFiles
	): array
	{
		$analyser = $this->createAnalyser(
			$ignoreErrors,
			$reportUnmatchedIgnoredErrors
		);
		return $analyser->analyse([$this->getFileHelper()->normalizePath($filePath)], $onlyFiles);
	}

	/**
	 * @param string[]|array<array<string, string>> $ignoreErrors
	 * @param bool $reportUnmatchedIgnoredErrors
	 * @return Analyser
	 */
	private function createAnalyser(
		array $ignoreErrors,
		bool $reportUnmatchedIgnoredErrors = true
	): \PHPStan\Analyser\Analyser
	{
		$registry = new Registry([
			new AlwaysFailRule(),
		]);

		$traverser = new \PhpParser\NodeTraverser();
		$traverser->addVisitor(new \PhpParser\NodeVisitor\NameResolver());

		$broker = $this->createBroker();
		$printer = new \PhpParser\PrettyPrinter\Standard();
		$fileHelper = self::getContainer()->getByType(FileHelper::class);

		/** @var RelativePathHelper $relativePathHelper */
		$relativePathHelper = self::getContainer()->getService('relativePathHelper');
		$phpDocStringResolver = self::getContainer()->getByType(PhpDocStringResolver::class);
		$typeSpecifier = $this->createTypeSpecifier($printer, $broker);
		return new Analyser(
			$this->createScopeFactory($broker, $typeSpecifier),
			new DirectParser(new \PhpParser\Parser\Php7(new \PhpParser\Lexer()), $traverser),
			$registry,
			new NodeScopeResolver(
				$broker,
				$this->getParser(),
				new FileTypeMapper($this->getParser(), $phpDocStringResolver, $this->createMock(Cache::class), new AnonymousClassNameHelper($fileHelper, $relativePathHelper), new \PHPStan\PhpDoc\TypeNodeResolver([])),
				$fileHelper,
				$typeSpecifier,
				false,
				false,
				true,
				[]
			),
			$fileHelper,
			$ignoreErrors,
			$reportUnmatchedIgnoredErrors,
			50
		);
	}

}
