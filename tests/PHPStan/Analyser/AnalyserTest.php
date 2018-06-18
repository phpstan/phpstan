<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PHPStan\Broker\AnonymousClassNameHelper;
use PHPStan\Cache\Cache;
use PHPStan\File\FileHelper;
use PHPStan\Parser\DirectParser;
use PHPStan\PhpDoc\PhpDocStringResolver;
use PHPStan\Rules\AlwaysFailRule;
use PHPStan\Rules\Registry;
use PHPStan\Type\FileTypeMapper;

class AnalyserTest extends \PHPStan\Testing\TestCase
{

	public function testReturnErrorIfIgnoredMessagesDoesNotOccur(): void
	{
		$result = $this->runAnalyser(['#Unknown error#'], null, true, __DIR__ . '/data/empty/empty.php', false);
		$this->assertSame([
			'Ignored error pattern #Unknown error# was not matched in reported errors.',
		], $result);
	}

	public function testDoNotReturnErrorIfIgnoredMessagesDoesNotOccurWithReportUnmatchedIgnoredErrorsOff(): void
	{
		$result = $this->runAnalyser(['#Unknown error#'], null, false, __DIR__ . '/data/empty/empty.php', false);
		$this->assertEmpty($result);
	}

	public function testDoNotReturnErrorIfIgnoredMessagesDoNotOccurWhileAnalysingIndividualFiles(): void
	{
		$result = $this->runAnalyser(['#Unknown error#'], null, true, __DIR__ . '/data/empty/empty.php', true);
		$this->assertEmpty($result);
	}

	public function testReportInvalidIgnorePatternEarly(): void
	{
		$result = $this->runAnalyser(['#Regexp syntax error'], null, true, __DIR__ . '/data/parse-error.php', false);
		$this->assertSame([
			"No ending delimiter '#' found in pattern: #Regexp syntax error",
		], $result);
	}

	public function testNonexistentBootstrapFile(): void
	{
		$result = $this->runAnalyser([], __DIR__ . '/foo.php', true, __DIR__ . '/data/empty/empty.php', false);
		$this->assertCount(1, $result);
		$this->assertContains('does not exist', $result[0]);
	}

	public function testBootstrapFile(): void
	{
		$result = $this->runAnalyser([], __DIR__ . '/data/bootstrap.php', true, __DIR__ . '/data/empty/empty.php', false);
		$this->assertEmpty($result);
		$this->assertSame('fooo', PHPSTAN_TEST_CONSTANT);
	}

	public function testBootstrapFileWithAnError(): void
	{
		$result = $this->runAnalyser([], __DIR__ . '/data/bootstrap-error.php', true, __DIR__ . '/data/empty/empty.php', false);
		$this->assertCount(1, $result);
		$this->assertSame([
			'Call to undefined function BootstrapError\doFoo()',
		], $result);
	}

	public function testFileWithAnIgnoredError(): void
	{
		$result = $this->runAnalyser(['#Fail\.#'], null, true, __DIR__ . '/data/bootstrap-error.php', false);
		$this->assertEmpty($result);
	}

	public function testIgnoringBrokenConfigurationDoesNotWork(): void
	{
		$result = $this->runAnalyser(['#was not found while trying to analyse it#'], null, true, __DIR__ . '/../../notAutoloaded/Baz.php', false);
		$this->assertCount(2, $result);
		assert($result[0] instanceof Error);
		$this->assertSame('Class PHPStan\Tests\Baz was not found while trying to analyse it - autoloading is probably not configured properly.', $result[0]->getMessage());
		$this->assertSame('Error message "Class PHPStan\Tests\Baz was not found while trying to analyse it - autoloading is probably not configured properly." cannot be ignored, use excludes_analyse instead.', $result[1]);
	}

	/**
	 * @param string[] $ignoreErrors
	 * @param string|null $bootstrapFile
	 * @param bool $reportUnmatchedIgnoredErrors
	 * @param string $filePath
	 * @param bool $onlyFiles
	 * @return string[]|\PHPStan\Analyser\Error[]
	 */
	private function runAnalyser(
		array $ignoreErrors,
		?string $bootstrapFile = null,
		bool $reportUnmatchedIgnoredErrors,
		string $filePath,
		bool $onlyFiles
	): array
	{
		$analyser = $this->createAnalyser(
			$ignoreErrors,
			$bootstrapFile,
			$reportUnmatchedIgnoredErrors
		);
		return $analyser->analyse([$this->getFileHelper()->normalizePath($filePath)], $onlyFiles);
	}

	/**
	 * @param string[] $ignoreErrors
	 * @param string|null $bootstrapFile
	 * @param bool $reportUnmatchedIgnoredErrors
	 * @return Analyser
	 */
	private function createAnalyser(
		array $ignoreErrors,
		?string $bootstrapFile = null,
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
		$phpDocStringResolver = self::getContainer()->getByType(PhpDocStringResolver::class);
		$typeSpecifier = $this->createTypeSpecifier($printer, $broker);
		$analyser = new Analyser(
			$this->createScopeFactory($broker, $typeSpecifier),
			new DirectParser(new \PhpParser\Parser\Php7(new \PhpParser\Lexer()), $traverser),
			$registry,
			new NodeScopeResolver(
				$broker,
				$this->getParser(),
				new FileTypeMapper($this->getParser(), $phpDocStringResolver, $this->createMock(Cache::class), new AnonymousClassNameHelper($fileHelper)),
				$fileHelper,
				$typeSpecifier,
				false,
				false,
				[]
			),
			$fileHelper,
			$ignoreErrors,
			$bootstrapFile,
			$reportUnmatchedIgnoredErrors,
			50
		);

		return $analyser;
	}

}
