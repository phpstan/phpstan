<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PHPStan\File\FileHelper;

class AnalyserTraitsIntegrationTest extends \PHPStan\Testing\TestCase
{

	/** @var \PHPStan\File\FileHelper */
	private $fileHelper;

	protected function setUp(): void
	{
		$this->fileHelper = self::getContainer()->getByType(FileHelper::class);
	}

	public function testMethodIsInClassUsingTrait(): void
	{
		$errors = $this->runAnalyse([
			__DIR__ . '/traits/Foo.php',
			__DIR__ . '/traits/FooTrait.php',
		]);
		self::assertEmpty($errors);
	}

	public function testMethodDoesNotExist(): void
	{
		$errors = $this->runAnalyse([
			__DIR__ . '/traits/Bar.php',
			__DIR__ . '/traits/FooTrait.php',
		]);
		self::assertCount(1, $errors);
		$error = $errors[0];
		self::assertSame('Call to an undefined method AnalyseTraits\Bar::doFoo().', $error->getMessage());
		self::assertSame(
			sprintf('%s (in context of class AnalyseTraits\Bar)', $this->fileHelper->normalizePath(__DIR__ . '/traits/FooTrait.php')),
			$error->getFile()
		);
		self::assertSame(10, $error->getLine());
	}

	public function testNestedTraits(): void
	{
		$errors = $this->runAnalyse([
			__DIR__ . '/traits/NestedBar.php',
			__DIR__ . '/traits/NestedFooTrait.php',
			__DIR__ . '/traits/FooTrait.php',
		]);
		self::assertCount(2, $errors);
		$firstError = $errors[0];
		self::assertSame('Call to an undefined method AnalyseTraits\NestedBar::doFoo().', $firstError->getMessage());
		self::assertSame(
			sprintf('%s (in context of class AnalyseTraits\NestedBar)', $this->fileHelper->normalizePath(__DIR__ . '/traits/FooTrait.php')),
			$firstError->getFile()
		);
		self::assertSame(10, $firstError->getLine());

		$secondError = $errors[1];
		self::assertSame('Call to an undefined method AnalyseTraits\NestedBar::doNestedFoo().', $secondError->getMessage());
		self::assertSame(
			sprintf('%s (in context of class AnalyseTraits\NestedBar)', $this->fileHelper->normalizePath(__DIR__ . '/traits/NestedFooTrait.php')),
			$secondError->getFile()
		);
		self::assertSame(12, $secondError->getLine());
	}

	public function testTraitsAreNotAnalysedDirectly(): void
	{
		$errors = $this->runAnalyse([__DIR__ . '/traits/FooTrait.php']);
		self::assertEmpty($errors);
		$errors = $this->runAnalyse([__DIR__ . '/traits/NestedFooTrait.php']);
		self::assertEmpty($errors);
	}

	public function testClassAndTraitInTheSameFile(): void
	{
		$errors = $this->runAnalyse([__DIR__ . '/traits/classAndTrait.php']);
		self::assertEmpty($errors);
	}

	public function testTraitMethodAlias(): void
	{
		$errors = $this->runAnalyse([__DIR__ . '/traits/trait-aliases.php']);
		self::assertEmpty($errors);
	}

	public function testFindErrorsInTrait(): void
	{
		$errors = $this->runAnalyse([__DIR__ . '/traits/trait-error.php']);
		self::assertCount(3, $errors);
		self::assertSame('Undefined variable: $undefined', $errors[0]->getMessage());
		self::assertSame('Call to an undefined method TraitErrors\MyClass::undefined().', $errors[1]->getMessage());
		self::assertSame('Undefined variable: $undefined', $errors[2]->getMessage());
	}

	public function testTraitInAnonymousClass(): void
	{
		$errors = $this->runAnalyse(
			[
				__DIR__ . '/traits/AnonymousClassUsingTrait.php',
				__DIR__ . '/traits/TraitWithTypeSpecification.php',
			]
		);
		self::assertCount(1, $errors);
		self::assertContains('Access to an undefined property', $errors[0]->getMessage());
		self::assertSame(18, $errors[0]->getLine());
	}

	public function testDuplicateMethodDefinition(): void
	{
		$errors = $this->runAnalyse([__DIR__ . '/traits/duplicateMethod/Lesson.php']);
		self::assertCount(0, $errors);
	}

	public function testWrongPropertyType(): void
	{
		$errors = $this->runAnalyse([__DIR__ . '/traits/wrongProperty/Foo.php']);
		self::assertCount(2, $errors);
		self::assertSame(15, $errors[0]->getLine());
		self::assertSame(
			$this->fileHelper->normalizePath(__DIR__ . '/traits/wrongProperty/Foo.php'),
			$errors[0]->getFile()
		);
		self::assertSame('Property TraitsWrongProperty\Foo::$id (int) does not accept string.', $errors[0]->getMessage());

		self::assertSame(17, $errors[1]->getLine());
		self::assertSame(
			$this->fileHelper->normalizePath(__DIR__ . '/traits/wrongProperty/Foo.php'),
			$errors[1]->getFile()
		);
		self::assertSame('Property TraitsWrongProperty\Foo::$bar (Ipsum) does not accept int.', $errors[1]->getMessage());
	}

	public function testReturnThis(): void
	{
		$errors = $this->runAnalyse([__DIR__ . '/traits/returnThis/Bar.php']);
		self::assertCount(2, $errors);
		self::assertSame(10, $errors[0]->getLine());
		self::assertSame('Call to an undefined method TraitsReturnThis\Foo::doFoo().', $errors[0]->getMessage());
		self::assertSame(11, $errors[1]->getLine());
		self::assertSame('Call to an undefined method TraitsReturnThis\Foo::doFoo().', $errors[1]->getMessage());
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
		$analyser = self::getContainer()->getByType(Analyser::class);
		/** @var \PHPStan\Analyser\Error[] $errors */
		$errors = $analyser->analyse($files, false);
		return $errors;
	}

}
