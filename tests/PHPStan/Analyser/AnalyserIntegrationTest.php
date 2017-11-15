<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PHPStan\File\FileHelper;

class AnalyserIntegrationTest extends \PHPStan\Testing\TestCase
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
		$this->assertCount(2, $errors);
		$this->assertSame('Function PHPStan\Tests\foo not found while trying to analyse it - autoloading is probably not configured properly.', $errors[0]->getMessage());
		$this->assertSame(5, $errors[0]->getLine());
		$this->assertSame('Function doSomething not found.', $errors[1]->getMessage());
		$this->assertSame(7, $errors[1]->getLine());
	}

	public function testAnonymousClassWithInheritedConstructor()
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/anonymous-class-with-inherited-constructor.php');
		$this->assertCount(0, $errors);
	}

	public function testNestedFunctionCallsDoNotCauseExcessiveFunctionNesting()
	{
		if (extension_loaded('xdebug')) {
			$this->markTestSkipped('This test takes too long with XDebug enabled.');
		}
		$errors = $this->runAnalyse(__DIR__ . '/data/nested-functions.php');
		$this->assertCount(0, $errors);
	}

	public function testExtendingUnknownClass()
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/extending-unknown-class.php');
		$this->assertCount(1, $errors);
		$this->assertNull($errors[0]->getLine());
		$this->assertSame('Class ExtendingUnknownClass\Bar not found and could not be autoloaded.', $errors[0]->getMessage());
	}

	public function testInfiniteRecursionWithCallable()
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/Foo-callable.php');
		$this->assertCount(0, $errors);
	}

	public function testClassExtendingNotAutoloadedClass()
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/class-autoloading.php');
		$this->assertCount(1, $errors);
		$this->assertSame('Class PHPStan\Tests\Baz not found and could not be autoloaded.', $errors[0]->getMessage());
	}

	/**
	 * @param string $file
	 * @return \PHPStan\Analyser\Error[]
	 */
	private function runAnalyse(string $file): array
	{
		$file = $this->getFileHelper()->normalizePath($file);
		/** @var \PHPStan\Analyser\Analyser $analyser */
		$analyser = $this->getContainer()->getByType(Analyser::class);
		/** @var \PHPStan\File\FileHelper $fileHelper */
		$fileHelper = $this->getContainer()->getByType(FileHelper::class);
		/** @var \PHPStan\Analyser\Error[] $errors */
		$errors = $analyser->analyse([$file], false);
		foreach ($errors as $error) {
			$this->assertSame($fileHelper->normalizePath($file), $error->getFile());
		}

		return $errors;
	}

}
