<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PHPStan\File\FileHelper;

class AnalyserTraitsIntegrationTest extends \PHPStan\Testing\TestCase
{

	/**
	 * @var \PHPStan\File\FileHelper
	 */
	private $fileHelper;

	protected function setUp()
	{
		$this->fileHelper = $this->getContainer()->getByType(FileHelper::class);
	}

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
			sprintf('%s (in context of class AnalyseTraits\Bar)', $this->fileHelper->normalizePath(__DIR__ . '/traits/FooTrait.php')),
			$error->getFile()
		);
		$this->assertSame(10, $error->getLine());
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
			sprintf('%s (in context of class AnalyseTraits\NestedBar)', $this->fileHelper->normalizePath(__DIR__ . '/traits/FooTrait.php')),
			$firstError->getFile()
		);
		$this->assertSame(10, $firstError->getLine());

		$secondError = $errors[1];
		$this->assertSame('Call to an undefined method AnalyseTraits\NestedBar::doNestedFoo().', $secondError->getMessage());
		$this->assertSame(
			sprintf('%s (in context of class AnalyseTraits\NestedBar)', $this->fileHelper->normalizePath(__DIR__ . '/traits/NestedFooTrait.php')),
			$secondError->getFile()
		);
		$this->assertSame(12, $secondError->getLine());
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

	public function testFindErrorsInTrait()
	{
		$errors = $this->runAnalyse([__DIR__ . '/traits/trait-error.php']);
		$this->assertCount(3, $errors);
		$this->assertSame('Undefined variable: $undefined', $errors[0]->getMessage());
		$this->assertSame('Call to an undefined method TraitErrors\MyClass::undefined().', $errors[1]->getMessage());
		$this->assertSame('Undefined variable: $undefined', $errors[2]->getMessage());
	}

	public function testTraitInAnonymousClass()
	{
		$errors = $this->runAnalyse(
			[
				__DIR__ . '/traits/AnonymousClassUsingTrait.php',
				__DIR__ . '/traits/TraitWithTypeSpecification.php',
			]
		);
		$this->assertEmpty($errors);
	}

	/**
	 * @param string[] $files
	 * @return \PHPStan\Analyser\Error[]
	 */
	private function runAnalyse(array $files): array
	{
		$files = array_map(function (string $file): string {
			return $this->getFileHelper()->normalizePath($file);
		}, $files);
		/** @var \PHPStan\Analyser\Analyser $analyser */
		$analyser = $this->getContainer()->getByType(Analyser::class);
		/** @var \PHPStan\Analyser\Error[] $errors */
		$errors = $analyser->analyse($files, false);
		return $errors;
	}

}
