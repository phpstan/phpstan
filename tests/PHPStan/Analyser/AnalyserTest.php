<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PHPStan\File\FileExcluder;
use PHPStan\File\FileHelper;
use PHPStan\Parser\DirectParser;
use PHPStan\Rules\AlwaysFailRule;
use PHPStan\Rules\Registry;
use PHPStan\Type\FileTypeMapper;

class AnalyserTest extends \PHPStan\TestCase
{
    public function dataExclude(): array
    {
        return [
            [
                __DIR__ . '/data/parse-error.php',
                [__DIR__],
                [],
                0,
            ],
            [
                __DIR__ . '/data/func-call.php',
                [],
                [],
                0,
            ],
            [
                __DIR__ . '/data/parse-error.php',
                [__DIR__ . '/*'],
                [],
                0,
            ],
            [
                __DIR__ . '/data/parse-error.php',
                [__DIR__ . '/data/?a?s?-error.?h?'],
                [],
                0,
            ],
            [
                __DIR__ . '/data/parse-error.php',
                [__DIR__ . '/data/[pP]arse-[eE]rror.ph[pP]'],
                [],
                0,
            ],
            [
                __DIR__ . '/data/parse-error.php',
                ['tests/PHPStan/Analyser/data'],
                [],
                0,
            ],
            [
                __DIR__ . '/data/parse-error.php',
                [__DIR__ . '/aaa'],
                [],
                1,
            ],
            [
                __DIR__ . '/data/parse-error.php',
                [__DIR__ . '/aaa'],
                [
                    '#Syntax error#',
                ],
                0,
            ],
        ];
    }

    /**
     * @dataProvider dataExclude
     * @param string $filePath
     * @param string[] $analyseExcludes
     * @param string[] $ignoreErrors
     * @param int $errorsCount
     */
    public function testExclude(string $filePath, array $analyseExcludes, array $ignoreErrors, int $errorsCount)
    {
        $analyser = $this->createAnalyser($analyseExcludes, $ignoreErrors);
        $result = $analyser->analyse([$filePath], !is_file($filePath));
        $this->assertInternalType('array', $result);
        $this->assertCount($errorsCount, $result);
    }

    public function testReturnErrorIfIgnoredMessagesDoesNotOccur()
    {
        $analyser = $this->createAnalyser([], ['#Unknown error#']);
        $result = $analyser->analyse([__DIR__ . '/data/empty/empty.php'], false);
        $this->assertInternalType('array', $result);
        $this->assertSame([
            'Ignored error pattern #Unknown error# was not matched in reported errors.',
        ], $result);
    }

    public function testDoNotReturnErrorIfIgnoredMessagesDoesNotOccurWithReportUnmatchedIgnoredErrorsOff()
    {
        $analyser = $this->createAnalyser([], ['#Unknown error#'], null, false);
        $result = $analyser->analyse([__DIR__ . '/data/empty/empty.php'], false);
        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }

    public function testDoNotReturnErrorIfIgnoredMessagesDoNotOccurWhileAnalysingIndividualFiles()
    {
        $analyser = $this->createAnalyser([], ['#Unknown error#']);
        $result = $analyser->analyse([__DIR__ . '/data/empty/empty.php'], true);
        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }

    public function testReportInvalidIgnorePatternEarly()
    {
        $analyser = $this->createAnalyser([], ['#Regexp syntax error']);
        $result = $analyser->analyse([__DIR__ . '/data/parse-error.php'], false);
        $this->assertInternalType('array', $result);
        $this->assertSame([
            "No ending delimiter '#' found in pattern: #Regexp syntax error",
        ], $result);
    }

    public function testNonexistentBootstrapFile()
    {
        $analyser = $this->createAnalyser([], [], __DIR__ . '/foo.php');
        $result = $analyser->analyse([__DIR__ . '/data/empty/empty.php'], false);
        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
        $this->assertContains('does not exist', $result[0]);
    }

    public function testBootstrapFile()
    {
        $analyser = $this->createAnalyser([], [], __DIR__ . '/data/bootstrap.php');
        $result = $analyser->analyse([__DIR__ . '/data/empty/empty.php'], false);
        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
        $this->assertSame('fooo', PHPSTAN_TEST_CONSTANT);
    }

    public function testBootstrapFileWithAnError()
    {
        $analyser = $this->createAnalyser([], [], __DIR__ . '/data/bootstrap-error.php');
        $result = $analyser->analyse([__DIR__ . '/data/empty/empty.php'], false);
        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
        $this->assertSame([
            'Call to undefined function BootstrapError\doFoo()',
        ], $result);
    }

    /**
     * @param string[] $analyseExcludes
     * @param string[] $ignoreErrors
     * @param string|null $bootstrapFile
     * @param bool $reportUnmatchedIgnoredErrors
     * @return Analyser
     */
    private function createAnalyser(
        array $analyseExcludes,
        array $ignoreErrors,
        string $bootstrapFile = null,
        bool $reportUnmatchedIgnoredErrors = true
    ): \PHPStan\Analyser\Analyser {
        $registry = new Registry([
            new AlwaysFailRule(),
        ]);

        $traverser = new \PhpParser\NodeTraverser();
        $traverser->addVisitor(new \PhpParser\NodeVisitor\NameResolver());

        $broker = $this->createBroker();
        $printer = new \PhpParser\PrettyPrinter\Standard();
        $fileHelper = $this->getContainer()->getByType(FileHelper::class);
        $fileExcluder = new FileExcluder($fileHelper, $analyseExcludes);
        $analyser = new Analyser(
            $broker,
            new DirectParser(new \PhpParser\Parser\Php7(new \PhpParser\Lexer()), $traverser),
            $registry,
            new NodeScopeResolver(
                $broker,
                $this->getParser(),
                $printer,
                new FileTypeMapper($this->getParser(), $this->createMock(\Nette\Caching\Cache::class), true),
                new TypeSpecifier($printer),
                $fileExcluder,
                false,
                false,
                false,
                []
            ),
            $printer,
            $fileExcluder,
            $ignoreErrors,
            $bootstrapFile,
            $fileHelper,
            $reportUnmatchedIgnoredErrors
        );

        return $analyser;
    }
}
