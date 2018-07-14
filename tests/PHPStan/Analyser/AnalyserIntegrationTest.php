<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PHPStan\Broker\Broker;
use PHPStan\File\FileHelper;
use PHPStan\Reflection\SignatureMap\SignatureMapProvider;

class AnalyserIntegrationTest extends \PHPStan\Testing\TestCase
{

	public function testUndefinedVariableFromAssignErrorHasLine(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/undefined-variable-assign.php');
		self::assertCount(1, $errors);
		$error = $errors[0];
		self::assertSame('Undefined variable: $bar', $error->getMessage());
		self::assertSame(3, $error->getLine());
	}

	public function testMissingPropertyAndMethod(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/../../notAutoloaded/Foo.php');
		self::assertCount(1, $errors);
		$error = $errors[0];
		self::assertContains('Property $fooProperty was not found in reflection of class PHPStan\Tests\Foo - probably the wrong version of class is autoloaded. The currently loaded version is at', $error->getMessage());
		self::assertNull($error->getLine());
	}

	public function testMissingClassErrorAboutMisconfiguredAutoloader(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/../../notAutoloaded/Bar.php');
		self::assertCount(1, $errors);
		$error = $errors[0];
		self::assertSame('Class PHPStan\Tests\Bar was not found while trying to analyse it - autoloading is probably not configured properly.', $error->getMessage());
		self::assertNull($error->getLine());
	}

	public function testMissingFunctionErrorAboutMisconfiguredAutoloader(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/../../notAutoloaded/functionFoo.php');
		self::assertCount(2, $errors);
		self::assertSame('Function PHPStan\Tests\foo not found while trying to analyse it - autoloading is probably not configured properly.', $errors[0]->getMessage());
		self::assertSame(5, $errors[0]->getLine());
		self::assertSame('Function doSomething not found.', $errors[1]->getMessage());
		self::assertSame(7, $errors[1]->getLine());
	}

	public function testAnonymousClassWithInheritedConstructor(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/anonymous-class-with-inherited-constructor.php');
		self::assertCount(0, $errors);
	}

	public function testNestedFunctionCallsDoNotCauseExcessiveFunctionNesting(): void
	{
		if (extension_loaded('xdebug')) {
			self::markTestSkipped('This test takes too long with XDebug enabled.');
		}
		$errors = $this->runAnalyse(__DIR__ . '/data/nested-functions.php');
		self::assertCount(0, $errors);
	}

	public function testExtendingUnknownClass(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/extending-unknown-class.php');
		self::assertCount(1, $errors);
		self::assertNull($errors[0]->getLine());
		self::assertSame('Class ExtendingUnknownClass\Bar not found and could not be autoloaded.', $errors[0]->getMessage());
	}

	public function testExtendingKnownClassWithCheck(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/extending-known-class-with-check.php');
		self::assertCount(1, $errors);
		self::assertSame('Class ExtendingKnownClassWithCheck\Bar not found.', $errors[0]->getMessage());
		self::assertSame(5, $errors[0]->getLine());

		$broker = self::getContainer()->getByType(Broker::class);
		self::assertTrue($broker->hasClass(\ExtendingKnownClassWithCheck\Foo::class));
	}

	public function testInfiniteRecursionWithCallable(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/Foo-callable.php');
		self::assertCount(0, $errors);
	}

	public function testClassThatExtendsUnknownClassIn3rdPartyPropertyTypeShouldNotCauseAutoloading(): void
	{
		// no error about PHPStan\Tests\Baz not being able to be autoloaded
		$errors = $this->runAnalyse(__DIR__ . '/data/ExtendsClassWithUnknownPropertyType.php');
		self::assertCount(1, $errors);
		//self::assertSame(11, $errors[0]->getLine());
		self::assertSame('Call to an undefined method ExtendsClassWithUnknownPropertyType::foo().', $errors[0]->getMessage());
	}

	public function testAnonymousClassesWithComments(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/AnonymousClassesWithComments.php');
		self::assertCount(3, $errors);
		foreach ($errors as $error) {
			self::assertContains('Call to an undefined method', $error->getMessage());
		}
	}

	public function testUniversalObjectCrateIssue(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/universal-object-crate.php');
		self::assertCount(1, $errors);
		self::assertSame('Parameter #1 $i of method UniversalObjectCrate\Foo::doBaz() expects int, string given.', $errors[0]->getMessage());
		self::assertSame(19, $errors[0]->getLine());
	}

	public function testCustomFunctionWithNameEquivalentInSignatureMap(): void
	{
		$signatureMapProvider = self::getContainer()->getByType(SignatureMapProvider::class);
		if (!$signatureMapProvider->hasFunctionSignature('bcompiler_write_file')) {
			self::fail();
		}
		require_once __DIR__ . '/data/custom-function-in-signature-map.php';
		$errors = $this->runAnalyse(__DIR__ . '/data/custom-function-in-signature-map.php');
		self::assertCount(0, $errors);
	}

	/**
	 * @param string $file
	 * @return \PHPStan\Analyser\Error[]
	 */
	private function runAnalyse(string $file): array
	{
		$file = $this->getFileHelper()->normalizePath($file);
		/** @var \PHPStan\Analyser\Analyser $analyser */
		$analyser = self::getContainer()->getByType(Analyser::class);
		/** @var \PHPStan\File\FileHelper $fileHelper */
		$fileHelper = self::getContainer()->getByType(FileHelper::class);
		/** @var \PHPStan\Analyser\Error[] $errors */
		$errors = $analyser->analyse([$file], false);
		foreach ($errors as $error) {
			self::assertSame($fileHelper->normalizePath($file), $error->getFile());
		}

		return $errors;
	}

}
