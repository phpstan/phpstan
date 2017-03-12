<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

class AnalyserTraitsIntegrationTest extends \PHPStan\TestCase
{
    public function testMethodIsInClassUsingTrait()
    {
        $errors = $this->runAnalyse([
            __DIR__ . '/traits/Foo.php',
            __DIR__ . '/traits/FooTrait.php',
        ]);
        $this->assertEmpty($errors);
    }

    public function testMethodDoesNotExist()
    {
        $errors = $this->runAnalyse([
            __DIR__ . '/traits/Bar.php',
            __DIR__ . '/traits/FooTrait.php',
        ]);
        $this->assertCount(1, $errors);
        $error = $errors[0];
        $this->assertSame('Call to an undefined method AnalyseTraits\Bar::doFoo().', $error->getMessage());
        $this->assertSame(
            sprintf('%s (in context of class AnalyseTraits\Bar)', __DIR__ . '/traits/FooTrait.php'),
            $error->getFile()
        );
        $this->assertSame(9, $error->getLine());
    }

    public function testNestedTraits()
    {
        $errors = $this->runAnalyse([
            __DIR__ . '/traits/NestedBar.php',
            __DIR__ . '/traits/NestedFooTrait.php',
            __DIR__ . '/traits/FooTrait.php',
        ]);
        $this->assertCount(2, $errors);
        $firstError = $errors[0];
        $this->assertSame('Call to an undefined method AnalyseTraits\NestedBar::doFoo().', $firstError->getMessage());
        $this->assertSame(
            sprintf('%s (in context of class AnalyseTraits\NestedBar)', __DIR__ . '/traits/FooTrait.php'),
            $firstError->getFile()
        );
        $this->assertSame(9, $firstError->getLine());

        $secondError = $errors[1];
        $this->assertSame('Call to an undefined method AnalyseTraits\NestedBar::doNestedFoo().', $secondError->getMessage());
        $this->assertSame(
            sprintf('%s (in context of class AnalyseTraits\NestedBar)', __DIR__ . '/traits/NestedFooTrait.php'),
            $secondError->getFile()
        );
        $this->assertSame(11, $secondError->getLine());
    }

    public function testTraitsAreNotAnalysedDirectly()
    {
        $errors = $this->runAnalyse([__DIR__ . '/traits/FooTrait.php']);
        $this->assertEmpty($errors);
        $errors = $this->runAnalyse([__DIR__ . '/traits/NestedFooTrait.php']);
        $this->assertEmpty($errors);
    }

    public function testClassAndTraitInTheSameFile()
    {
        $errors = $this->runAnalyse([__DIR__ . '/traits/classAndTrait.php']);
        $this->assertEmpty($errors);
    }

    public function testTraitMethodAlias()
    {
        $errors = $this->runAnalyse([__DIR__ . '/traits/trait-aliases.php']);
        $this->assertEmpty($errors);
    }

    /**
     * @param string[] $files
     * @return \PHPStan\Analyser\Error[]|string[]
     */
    private function runAnalyse(array $files): array
    {
        /** @var \PHPStan\Analyser\Analyser $analyser */
        $analyser = $this->getContainer()->get(Analyser::class);
        return $analyser->analyse($files, false);
    }
}
