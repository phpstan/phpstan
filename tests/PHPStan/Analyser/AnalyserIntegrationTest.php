<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PHPStan\File\FileHelper;

class AnalyserIntegrationTest extends \PHPStan\TestCase
{
    public function testUndefinedVariableFromAssignErrorHasLine()
    {
        $errors = $this->runAnalyse(__DIR__ . '/data/undefined-variable-assign.php');
        $this->assertCount(1, $errors);
        $error = $errors[0];
        $this->assertSame('Undefined variable: $bar', $error->getMessage());
        $this->assertSame(3, $error->getLine());
    }

    public function testMissingPropertyAndMethod()
    {
        $errors = $this->runAnalyse(__DIR__ . '/../../notAutoloaded/Foo.php');
        $this->assertCount(1, $errors);
        $error = $errors[0];
        $this->assertSame('Property $fooProperty was not found in reflection of class PHPStan\Tests\Foo - probably the wrong version of class is autoloaded.', $error->getMessage());
        $this->assertNull($error->getLine());
    }

    public function testMissingClassErrorAboutMisconfiguredAutoloader()
    {
        $errors = $this->runAnalyse(__DIR__ . '/../../notAutoloaded/Bar.php');
        $this->assertCount(1, $errors);
        $error = $errors[0];
        $this->assertSame('Class PHPStan\Tests\Bar was not found while trying to analyse it - autoloading is probably not configured properly.', $error->getMessage());
        $this->assertNull($error->getLine());
    }

    public function testMissingFunctionErrorAboutMisconfiguredAutoloader()
    {
        $errors = $this->runAnalyse(__DIR__ . '/../../notAutoloaded/functionFoo.php');
        $this->assertCount(1, $errors);
        $error = $errors[0];
        $this->assertSame('Function PHPStan\Tests\foo not found while trying to analyse it - autoloading is probably not configured properly.', $error->getMessage());
        $this->assertNull($error->getLine());
    }

    /**
     * @param string $file
     * @return \PHPStan\Analyser\Error[]|string[]
     */
    private function runAnalyse(string $file): array
    {
        /** @var \PHPStan\Analyser\Analyser $analyser */
        $analyser = $this->getContainer()->get(Analyser::class);
        /** @var \PHPStan\File\FileHelper $fileHelper */
        $fileHelper = $this->getContainer()->get(FileHelper::class);
        $errors = $analyser->analyse([$file], false);
        foreach ($errors as $error) {
            $this->assertSame($fileHelper->normalizePath($file), $error->getFile());
        }

        return $errors;
    }
}
