<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PHPStan\Parser\DirectParser;
use PHPStan\Rules\AlwaysFailRule;
use PHPStan\Rules\Registry;
use PHPStan\Type\FileTypeMapper;

class AnalyserTest extends \PHPStan\TestCase
{
    public function testDoNotReturnErrorIfIgnoredMessagesDoesNotOccurWithReportUnmatchedIgnoredErrorsOff()
    {
        $analyser = $this->createAnalyser(['#Unknown error#'], null, false);
        $result = $analyser->analyse([__DIR__ . '/data/empty/empty.php'], false);
        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }

    public function testDoNotReturnErrorIfIgnoredMessagesDoNotOccurWhileAnalysingIndividualFiles()
    {
        $analyser = $this->createAnalyser(['#Unknown error#']);
        $result = $analyser->analyse([__DIR__ . '/data/empty/empty.php'], true);
        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }

    /** skip todo
    public function testReportInvalidIgnorePatternEarly()
    {
        $analyser = $this->createAnalyser(['#Regexp syntax error']);
        $result = $analyser->analyse([__DIR__ . '/data/parse-error.php'], false);
        $this->assertInternalType('array', $result);
        $this->assertSame([
            "No ending delimiter '#' found in pattern: #Regexp syntax error",
        ], $result);
    }*/

    public function testNonexistentBootstrapFile()
    {
        $analyser = $this->createAnalyser([], __DIR__ . '/foo.php');
        $result = $analyser->analyse([__DIR__ . '/data/empty/empty.php'], false);
        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
        $this->assertContains('does not exist', $result[0]);
    }

    public function testBootstrapFile()
    {
        $analyser = $this->createAnalyser([], __DIR__ . '/data/bootstrap.php');
        $result = $analyser->analyse([__DIR__ . '/data/empty/empty.php'], false);
        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
        $this->assertSame('fooo', PHPSTAN_TEST_CONSTANT);
    }

    public function testBootstrapFileWithAnError()
    {
        $analyser = $this->createAnalyser([], __DIR__ . '/data/bootstrap-error.php');
        $result = $analyser->analyse([__DIR__ . '/data/empty/empty.php'], false);
        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
        $this->assertSame([
            'Call to undefined function BootstrapError\doFoo()',
        ], $result);
    }

    /**
     * @param string[] $ignoreErrors
     * @param string|null $bootstrapFile
     * @param bool $reportUnmatchedIgnoredErrors
     * @return Analyser
     */
    private function createAnalyser(
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
        $analyser = new Analyser(
            $broker,
            new DirectParser(new \PhpParser\Parser\Php7(new \PhpParser\Lexer()), $traverser),
            $registry,
            new NodeScopeResolver(
                $broker,
                $this->getParser(),
                $printer,
                new FileTypeMapper($this->getParser(), new \Stash\Pool(new \Stash\Driver\Ephemeral()), true),
                new TypeSpecifier($printer),
                false,
                false,
                false,
                []
            ),
            $printer,
            $ignoreErrors,
            $bootstrapFile,
            $reportUnmatchedIgnoredErrors
        );

        return $analyser;
    }
}
