<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use Bug4288\MyClass;
use Bug4713\Service;
use ExtendingKnownClassWithCheck\Foo;
use PHPStan\Reflection\InitializerExprContext;
use PHPStan\Reflection\InitializerExprTypeResolver;
use PHPStan\Reflection\SignatureMap\SignatureMapProvider;
use PHPStan\Testing\PHPStanTestCase;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPUnit\Framework\Attributes\RequiresPhp;
use function extension_loaded;
use function sprintf;
use const PHP_VERSION_ID;

class AnalyserIntegrationTest extends PHPStanTestCase
{

	public function testUndefinedVariableFromAssignErrorHasLine(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/undefined-variable-assign.php');
		$this->assertCount(2, $errors);
		$error = $errors[0];
		$this->assertSame('Undefined variable: $bar', $error->getMessage());
		$this->assertSame(3, $error->getLine());

		$error = $errors[1];
		$this->assertSame('Variable $foo might not be defined.', $error->getMessage());
		$this->assertSame(6, $error->getLine());
	}

	public function testMissingPropertyAndMethod(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/../../notAutoloaded/Foo.php');
		$this->assertNoErrors($errors);
	}

	public function testMissingClassErrorAboutMisconfiguredAutoloader(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/../../notAutoloaded/Bar.php');
		$this->assertNoErrors($errors);
	}

	public function testMissingFunctionErrorAboutMisconfiguredAutoloader(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/../../notAutoloaded/functionFoo.php');
		$this->assertCount(1, $errors);
		$this->assertSame('Function doSomething not found.', $errors[0]->getMessage());
		$this->assertSame(7, $errors[0]->getLine());
	}

	public function testAnonymousClassWithInheritedConstructor(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/anonymous-class-with-inherited-constructor.php');
		$this->assertNoErrors($errors);
	}

	public function testNestedFunctionCallsDoNotCauseExcessiveFunctionNesting(): void
	{
		if (extension_loaded('xdebug')) {
			$this->markTestSkipped('This test takes too long with Xdebug enabled.');
		}
		$errors = $this->runAnalyse(__DIR__ . '/data/nested-functions.php');
		$this->assertNoErrors($errors);
	}

	public function testExtendingUnknownClass(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/extending-unknown-class.php');
		$this->assertCount(1, $errors);

		$this->assertSame(5, $errors[0]->getLine());
		$this->assertSame('Class ExtendingUnknownClass\Foo extends unknown class ExtendingUnknownClass\Bar.', $errors[0]->getMessage());
	}

	public function testExtendingKnownClassWithCheck(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/extending-known-class-with-check.php');
		$this->assertNoErrors($errors);

		$reflectionProvider = self::createReflectionProvider();
		$this->assertTrue($reflectionProvider->hasClass(Foo::class));
	}

	public function testInfiniteRecursionWithCallable(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/Foo-callable.php');
		$this->assertNoErrors($errors);
	}

	public function testClassThatExtendsUnknownClassIn3rdPartyPropertyTypeShouldNotCauseAutoloading(): void
	{
		// no error about PHPStan\Tests\Baz not being able to be autoloaded
		$errors = $this->runAnalyse(__DIR__ . '/data/ExtendsClassWithUnknownPropertyType.php');
		$this->assertCount(1, $errors);
		//$this->assertSame(11, $errors[0]->getLine());
		$this->assertSame('Call to an undefined method ExtendsClassWithUnknownPropertyType::foo().', $errors[0]->getMessage());
	}

	public function testAnonymousClassesWithComments(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/AnonymousClassesWithComments.php');
		$this->assertCount(3, $errors);
		foreach ($errors as $error) {
			$this->assertStringContainsString('Call to an undefined method', $error->getMessage());
		}
	}

	public function testUniversalObjectCrateIssue(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/universal-object-crate.php');
		$this->assertCount(1, $errors);
		$this->assertSame('Parameter #1 $i of method UniversalObjectCrate\Foo::doBaz() expects int, string given.', $errors[0]->getMessage());
		$this->assertSame(19, $errors[0]->getLine());
	}

	public function testCustomFunctionWithNameEquivalentInSignatureMap(): void
	{
		$signatureMapProvider = self::getContainer()->getByType(SignatureMapProvider::class);
		if (!$signatureMapProvider->hasFunctionSignature('bcompiler_write_file')) {
			$this->fail();
		}
		require_once __DIR__ . '/data/custom-function-in-signature-map.php';
		$errors = $this->runAnalyse(__DIR__ . '/data/custom-function-in-signature-map.php');
		$this->assertNoErrors($errors);
	}

	public function testAnonymousClassWithWrongFilename(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/anonymous-class-wrong-filename-regression.php');
		$this->assertCount(5, $errors);
		$this->assertStringContainsString('Method', $errors[0]->getMessage());
		$this->assertStringContainsString('has invalid return type', $errors[0]->getMessage());
		$this->assertSame(16, $errors[0]->getLine());
		$this->assertStringContainsString('Method', $errors[1]->getMessage());
		$this->assertStringContainsString('has invalid return type', $errors[1]->getMessage());
		$this->assertSame(16, $errors[1]->getLine());
		$this->assertSame('Instantiated class AnonymousClassWrongFilename\Bar not found.', $errors[2]->getMessage());
		$this->assertSame(18, $errors[2]->getLine());
		$this->assertStringContainsString('Parameter #1 $test of method', $errors[3]->getMessage());
		$this->assertStringContainsString('$this(AnonymousClassWrongFilename\Foo) given', $errors[3]->getMessage());
		$this->assertSame(23, $errors[3]->getLine());
		$this->assertSame('Call to method test() on an unknown class AnonymousClassWrongFilename\Bar.', $errors[4]->getMessage());
		$this->assertSame(24, $errors[4]->getLine());
	}

	public function testExtendsPdoStatementCrash(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/extends-pdo-statement.php');
		$this->assertNoErrors($errors);
	}

	public function testBug12803(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-12803.php');
		$this->assertNoErrors($errors);
	}

	public function testArrayDestructuringArrayDimFetch(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/array-destructuring-array-dim-fetch.php');
		$this->assertNoErrors($errors);
	}

	public function testNestedNamespaces(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/nested-namespaces.php');
		$this->assertCount(2, $errors);
		$this->assertSame('Property y\x::$baz has unknown class x\baz as its type.', $errors[0]->getMessage());
		$this->assertSame(15, $errors[0]->getLine());
		$this->assertSame('Parameter $baz of method y\x::__construct() has invalid type x\baz.', $errors[1]->getMessage());
		$this->assertSame(16, $errors[1]->getLine());
	}

	public function testClassExistsAutoloadingError(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/class-exists.php');
		$this->assertNoErrors($errors);
	}

	public function testCollectWarnings(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/declaration-warning.php');
		$this->assertCount(1, $errors);
		$this->assertSame('Parameter #1 $i of method DeclarationWarning\Bar::doFoo() is not optional.', $errors[0]->getMessage());
		$this->assertSame(22, $errors[0]->getLine());
	}

	public function testPropertyAssignIntersectionStaticTypeBug(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/property-assign-intersection-static-type-bug.php');
		$this->assertNoErrors($errors);
	}

	public function testBug2823(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-2823.php');
		$this->assertNoErrors($errors);
	}

	public function testTwoSameClassesInSingleFile(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/two-same-classes.php');
		$this->assertCount(5, $errors);

		$error = $errors[0];
		$this->assertSame('Property TwoSame\Foo::$prop (string) does not accept default value of type int.', $error->getMessage());
		$this->assertSame(9, $error->getLine());

		$error = $errors[1];
		$this->assertSame('Access to undefined constant TwoSame\Foo::FOO_CONST.', $error->getMessage());
		$this->assertSame(13, $error->getLine());

		$error = $errors[2];
		$this->assertSame('If condition is always false.', $error->getMessage());
		$this->assertSame(26, $error->getLine());

		$error = $errors[3];
		$this->assertSame('Property TwoSame\Foo::$prop (int) does not accept default value of type string.', $error->getMessage());
		$this->assertSame(33, $error->getLine());

		$error = $errors[4];
		$this->assertSame('Property TwoSame\Foo::$prop2 (int) does not accept default value of type string.', $error->getMessage());
		$this->assertSame(36, $error->getLine());
	}

	public function testBug6936(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-6936.php');
		$this->assertNoErrors($errors);
	}

	public function testBug3405(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-3405.php');
		$this->assertCount(1, $errors);
		$this->assertSame('Magic constant __TRAIT__ is always empty outside a trait.', $errors[0]->getMessage());
		$this->assertSame(16, $errors[0]->getLine());
	}

	public function testBug3415(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/../Rules/Methods/data/bug-3415.php');
		$this->assertNoErrors($errors);
	}

	public function testBug3415Two(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/../Rules/Methods/data/bug-3415-2.php');
		$this->assertNoErrors($errors);
	}

	public function testBug3468(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-3468.php');
		$this->assertNoErrors($errors);
	}

	public function testBug3686(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-3686.php');
		$this->assertNoErrors($errors);
	}

	#[RequiresPhp('>= 8.0')]
	public function testBug13352(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-13352.php');
		$this->assertNoErrors($errors);
	}

	public function testBug3379(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/nsrt/bug-3379.php');
		$this->assertCount(1, $errors);
		$this->assertSame('Constant SOME_UNKNOWN_CONST not found.', $errors[0]->getMessage());
	}

	public function testBug3798(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-3798.php');
		$this->assertNoErrors($errors);
	}

	public function testBug3909(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-3909.php');
		$this->assertNoErrors($errors);
	}

	public function testBug4097(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-4097.php');
		$this->assertNoErrors($errors);
	}

	public function testBug4300(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-4300.php');
		$this->assertCount(1, $errors);
		$this->assertSame('Comparison operation ">" between 0 and 0 is always false.', $errors[0]->getMessage());
		$this->assertSame(13, $errors[0]->getLine());
	}

	public function testBug4513(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-4513.php');
		$this->assertNoErrors($errors);
	}

	public function testBug1871(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-1871.php');
		$this->assertNoErrors($errors);
	}

	public function testBug3309(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-3309.php');
		$this->assertNoErrors($errors);
	}

	public function testBug11649(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-11649.php');
		$this->assertNoErrors($errors);
	}

	#[RequiresPhp('>= 8.0')]
	public function testBug6872(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-6872.php');
		$this->assertNoErrors($errors);
	}

	public function testBug3769(): void
	{
		require_once __DIR__ . '/../Rules/Generics/data/bug-3769.php';
		$errors = $this->runAnalyse(__DIR__ . '/../Rules/Generics/data/bug-3769.php');
		$this->assertNoErrors($errors);
	}

	public function testBug6301(): void
	{
		require_once __DIR__ . '/../Rules/Generics/data/bug-6301.php';
		$errors = $this->runAnalyse(__DIR__ . '/../Rules/Generics/data/bug-6301.php');
		$this->assertNoErrors($errors);
	}

	public function testBug3922(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-3922-integration.php');
		$this->assertNoErrors($errors);
	}

	public function testBug1843(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-1843.php');
		$this->assertNoErrors($errors);
	}

	public function testBug9711(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-9711.php');
		$this->assertCount(1, $errors);
		$this->assertSame('Function in_array invoked with 1 parameter, 2-3 required.', $errors[0]->getMessage());
	}

	public function testBug4713(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-4713.php');
		$this->assertCount(1, $errors);
		$this->assertSame('Method Bug4713\Service::createInstance() should return Bug4713\Service but returns object.', $errors[0]->getMessage());

		$reflectionProvider = self::createReflectionProvider();
		$class = $reflectionProvider->getClass(Service::class);
		$parameter = $class->getNativeMethod('createInstance')->getOnlyVariant()->getParameters()[0];
		$defaultValue = $parameter->getDefaultValue();
		$this->assertInstanceOf(ConstantStringType::class, $defaultValue);
		$this->assertSame(Service::class, $defaultValue->getValue());
	}

	public function testBug4288(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-4288.php');
		$this->assertNoErrors($errors);

		$reflectionProvider = self::createReflectionProvider();
		$class = $reflectionProvider->getClass(MyClass::class);
		$parameter = $class->getNativeMethod('paginate')->getOnlyVariant()->getParameters()[0];
		$defaultValue = $parameter->getDefaultValue();
		$this->assertInstanceOf(ConstantIntegerType::class, $defaultValue);
		$this->assertSame(10, $defaultValue->getValue());

		$nativeProperty = $class->getNativeReflection()->getProperty('test');
		$initializerExprTypeResolver = self::getContainer()->getByType(InitializerExprTypeResolver::class);
		$defaultValueType = $initializerExprTypeResolver->getType(
			$nativeProperty->getDefaultValueExpression(),
			InitializerExprContext::fromClassReflection($class->getNativeProperty('test')->getDeclaringClass()),
		);
		$this->assertInstanceOf(ConstantIntegerType::class, $defaultValueType);
		$this->assertSame(10, $defaultValueType->getValue());
	}

	public function testBug4702(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-4702.php');
		$this->assertNoErrors($errors);
	}

	public function testFunctionThatExistsOn72AndLater(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/ldap-exop-passwd.php');
		if (PHP_VERSION_ID < 80100) {
			$this->assertNoErrors($errors);
			return;
		}

		$this->assertCount(1, $errors);
		$this->assertSame('Parameter #1 $ldap of function ldap_exop_passwd expects LDAP\Connection, resource given.', $errors[0]->getMessage());
	}

	public function testBug4715(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-4715.php');
		$this->assertNoErrors($errors);
	}

	public function testBug4734(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-4734.php');
		$this->assertCount(3, $errors);

		$this->assertSame('Unsafe access to private property Bug4734\Foo::$httpMethodParameterOverride through static::.', $errors[0]->getMessage());
		$this->assertSame('Access to an undefined static property static(Bug4734\Foo)::$httpMethodParameterOverride3.', $errors[1]->getMessage());
		$this->assertSame('Access to an undefined property Bug4734\Foo::$httpMethodParameterOverride4.', $errors[2]->getMessage());
	}

	public function testBug5231(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-5231.php');
		$this->assertNotEmpty($errors);
	}

	public function testBug5231Two(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-5231_2.php');
		$this->assertNotEmpty($errors);
	}

	#[RequiresPhp('>= 8.1')]
	public function testBug12512(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-12512.php');
		$this->assertNoErrors($errors);
	}

	#[RequiresPhp('>= 8.0')]
	public function testBug13218(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-13218.php');
		$this->assertNoErrors($errors);
	}

	public function testBug5529(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/nsrt/bug-5529.php');
		$this->assertNoErrors($errors);
	}

	public function testBug5527(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-5527.php');
		$this->assertNoErrors($errors);
	}

	public function testBug5639(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-5639.php');
		$this->assertNoErrors($errors);
	}

	public function testBug5657(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-5657.php');
		$this->assertNoErrors($errors);
	}

	#[RequiresPhp('>= 8.0')]
	public function testBug5951(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-5951.php');
		$this->assertNoErrors($errors);
	}

	#[RequiresPhp('>= 8.1')]
	public function testEnums(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/enums-integration.php');
		$this->assertCount(3, $errors);
		$this->assertSame('Access to an undefined property EnumIntegrationTest\Foo::TWO::$value.', $errors[0]->getMessage());
		$this->assertSame(22, $errors[0]->getLine());
		$this->assertSame('Access to undefined constant EnumIntegrationTest\Bar::NONEXISTENT.', $errors[1]->getMessage());
		$this->assertSame(49, $errors[1]->getLine());
		$this->assertSame('Strict comparison using === between EnumIntegrationTest\Foo::ONE and EnumIntegrationTest\Foo::TWO will always evaluate to false.', $errors[2]->getMessage());
		$this->assertSame(79, $errors[2]->getLine());
	}

	public function testBug6255(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-6255.php');
		$this->assertNoErrors($errors);
	}

	public function testBug6300(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-6300.php');
		$this->assertCount(1, $errors);
		$this->assertSame('Call to an undefined method Bug6300\Bar::get().', $errors[0]->getMessage());
		$this->assertSame(27, $errors[0]->getLine());
	}

	public function testBug6466(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-6466.php');
		$this->assertNoErrors($errors);
	}

	#[RequiresPhp('>= 8.1')]
	public function testBug6494(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-6494.php');
		$this->assertNoErrors($errors);
	}

	public function testBug6253(): void
	{
		$errors = $this->runAnalyse(
			__DIR__ . '/data/bug-6253.php',
			[
				__DIR__ . '/data/bug-6253.php',
				__DIR__ . '/data/bug-6253-app-scope-trait.php',
				__DIR__ . '/data/bug-6253-collection-trait.php',
			],
		);
		$this->assertNoErrors($errors);
	}

	public function testBug6442(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-6442.php');
		$this->assertCount(2, $errors);
		$this->assertSame('Dumped type: \'Bug6442\\\B\'', $errors[0]->getMessage());
		$this->assertSame(9, $errors[0]->getLine());
		$this->assertSame('Dumped type: \'Bug6442\\\A\'', $errors[1]->getMessage());
		$this->assertSame(9, $errors[1]->getLine());
	}

	public function testBug13057(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-13057.php');
		$this->assertNoErrors($errors);
	}

	public function testBug6375(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-6375.php');
		$this->assertNoErrors($errors);
	}

	public function testBug6501(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-6501.php');
		$this->assertCount(1, $errors);
		$this->assertSame('PHPDoc tag @var with type R of Exception|stdClass is not subtype of native type stdClass.', $errors[0]->getMessage());
		$this->assertSame(24, $errors[0]->getLine());
	}

	#[RequiresPhp('>= 8.0')]
	public function testBug6114(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-6114.php');
		$this->assertNoErrors($errors);
	}

	public function testBug6681(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-6681.php');
		$this->assertNoErrors($errors);
	}

	public function testBug6212(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-6212.php');
		$this->assertNoErrors($errors);
	}

	public function testBug6740(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-6740-b.php');
		$this->assertNoErrors($errors);
	}

	public function testBug6866(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-6866.php');
		$this->assertNoErrors($errors);
	}

	public function testBug6649(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-6649.php');
		$this->assertNoErrors($errors);
	}

	public function testBug12778(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-12778.php');
		$this->assertNoErrors($errors);
	}

	public function testBug6842(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-6842.php');
		$this->assertCount(2, $errors);
		$this->assertSame('Generator expects value type T of DateTimeInterface, DateTime|DateTimeImmutable|T of DateTimeInterface given.', $errors[0]->getMessage());
		$this->assertSame(28, $errors[0]->getLine());

		$this->assertSame('Generator expects value type T of DateTimeInterface, DateTime|DateTimeImmutable|T of DateTimeInterface given.', $errors[1]->getMessage());
		$this->assertSame(54, $errors[1]->getLine());
	}

	#[RequiresPhp('>= 8.0')]
	public function testBug6896(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-6896.php');
		$this->assertCount(4, $errors);
		$this->assertSame('Generic type IteratorIterator<(int|string), mixed> in PHPDoc tag @return does not specify all template types of class IteratorIterator: TKey, TValue, TIterator', $errors[0]->getMessage());
		$this->assertSame(38, $errors[0]->getLine());
		$this->assertSame('Generic type LimitIterator<(int|string), mixed> in PHPDoc tag @return does not specify all template types of class LimitIterator: TKey, TValue, TIterator', $errors[1]->getMessage());
		$this->assertSame(38, $errors[1]->getLine());
		$this->assertSame('Method Bug6896\RandHelper::getPseudoRandomWithUrl() return type with generic class Bug6896\XIterator does not specify its types: TKey, TValue', $errors[2]->getMessage());
		$this->assertSame(38, $errors[2]->getLine());
		$this->assertSame('Method Bug6896\RandHelper::getPseudoRandomWithUrl() should return array<TRandKey of (int|string), TRandVal>|Bug6896\XIterator<TRandKey of (int|string), TRandVal>|IteratorIterator<TRandKey of (int|string), TRandVal>|LimitIterator<TRandKey of (int|string), TRandVal> but returns TRandList of array<TRandKey of (int|string), TRandVal>|Traversable<TRandKey of (int|string), TRandVal>.', $errors[3]->getMessage());
		$this->assertSame(42, $errors[3]->getLine());
	}

	public function testBug6940(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-6940.php');
		$this->assertCount(1, $errors);
		$this->assertSame('Loose comparison using == between array{} and array{} will always evaluate to true.', $errors[0]->getMessage());
		$this->assertSame(12, $errors[0]->getLine());
	}

	public function testBug1447(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-1447.php');
		$this->assertNoErrors($errors);
	}

	public function testBug5081(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-5081.php');
		$this->assertNoErrors($errors);
	}

	public function testBug1388(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-1388.php');
		$this->assertNoErrors($errors);
	}

	#[RequiresPhp('>= 8.0')]
	public function testBug4308(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-4308.php');
		$this->assertNoErrors($errors);
	}

	public function testBug4732(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-4732.php');
		$this->assertNoErrors($errors);
	}

	public function testBug6160(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-6160.php');
		$this->assertCount(2, $errors);
		$this->assertSame('Parameter #1 $flags of static method Bug6160\HelloWorld::split() expects 0|1|2, 94561 given.', $errors[0]->getMessage());
		$this->assertSame(19, $errors[0]->getLine());
		$this->assertSame('Parameter #1 $flags of static method Bug6160\HelloWorld::split() expects 0|1|2, \'sdf\' given.', $errors[1]->getMessage());
		$this->assertSame(23, $errors[1]->getLine());
	}

	public function testBug6979(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-6979.php');
		$this->assertNoErrors($errors);
	}

	public function testBug7030(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-7030.php');
		$this->assertCount(1, $errors);
		$this->assertSame('PHPDoc tag @method has invalid value (array  getItemsForID($id, $quantity, $shippingPostCode = null, $wholesalerList = null, $shippingLatitude =
	null, $shippingLongitude = null, $shippingNeutralShipping = null)): Unexpected token "\n * ", expected type at offset 193 on line 6', $errors[0]->getMessage());
	}

	#[RequiresPhp('>= 8.1')]
	public function testBug7012(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-7012.php');
		$this->assertNoErrors($errors);
	}

	#[RequiresPhp('>= 8.1')]
	public function testBug6192(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-6192.php');
		$this->assertNoErrors($errors);
	}

	public function testBug7068(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/nsrt/bug-7068.php');
		$this->assertNoErrors($errors);
	}

	#[RequiresPhp('>= 8.0')]
	public function testDiscussion6993(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/nsrt/bug-6993.php');
		$this->assertCount(1, $errors);
		$this->assertSame('Parameter #1 $specificable of method Bug6993\AndSpecificationValidator<Bug6993\TestSpecification,Bug6993\Foo>::isSatisfiedBy() expects Bug6993\Foo, Bug6993\Bar given.', $errors[0]->getMessage());
	}

	public function testBug7077(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-7077.php');
		$this->assertNoErrors($errors);
	}

	#[RequiresPhp('>= 8.0')]
	public function testBug7078(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/nsrt/bug-7078.php');
		$this->assertNoErrors($errors);
	}

	#[RequiresPhp('>= 8.0')]
	public function testBug7116(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-7116.php');
		$this->assertNoErrors($errors);
	}

	public function testBug3853(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/nsrt/bug-3853.php');
		$this->assertNoErrors($errors);
	}

	#[RequiresPhp('>= 8.1')]
	public function testBug7135(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-7135.php');
		$this->assertCount(1, $errors);
		$this->assertSame('Cannot create callable from the new operator.', $errors[0]->getMessage());
	}

	#[RequiresPhp('>= 8.0')]
	public function testDiscussion7124(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/discussion-7124.php');
		$this->assertCount(4, $errors);
		$this->assertSame('Parameter #2 $callback of function Discussion7124\filter expects callable(bool, 0|1|2=): bool, Closure(int, bool): bool given.', $errors[0]->getMessage());
		$this->assertSame(38, $errors[0]->getLine());
		$this->assertSame('Parameter #2 $callback of function Discussion7124\filter expects callable(bool, 0|1|2=): bool, Closure(int): bool given.', $errors[1]->getMessage());
		$this->assertSame(45, $errors[1]->getLine());
		$this->assertSame('Parameter #2 $callback of function Discussion7124\filter expects callable(0|1|2): bool, Closure(bool): bool given.', $errors[2]->getMessage());
		$this->assertSame(52, $errors[2]->getLine());
		$this->assertSame('Parameter #2 $callback of function Discussion7124\filter expects callable(bool): bool, Closure(int): bool given.', $errors[3]->getMessage());
		$this->assertSame(59, $errors[3]->getLine());
	}

	public function testBug7214(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-7214.php');
		$this->assertCount(1, $errors);
		$this->assertSame('Method Bug7214\HelloWorld::getFoo() has no return type specified.', $errors[0]->getMessage());
		$this->assertSame(6, $errors[0]->getLine());
	}

	public function testBug12327(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-12327.php');
		$this->assertCount(1, $errors);

		$this->assertSame('Class Bug12327\DoesNotMatter uses unknown trait Bug12327\ThisTriggersTheIssue.', $errors[0]->getMessage());
		$this->assertSame(15, $errors[0]->getLine());
	}

	public function testBug7215(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-7215.php');
		$this->assertNoErrors($errors);
	}

	public function testBug7094(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-7094.php');
		$this->assertCount(6, $errors);

		$this->assertSame('Parameter #2 $val of method Bug7094\Foo::setAttribute() contains unresolvable type.', $errors[0]->getMessage());
		$this->assertSame(74, $errors[0]->getLine());
		$this->assertSame('Parameter #2 $val of method Bug7094\Foo::setAttribute() expects string, int given.', $errors[1]->getMessage());
		$this->assertSame(75, $errors[1]->getLine());
		$this->assertSame('Parameter #2 $val of method Bug7094\Foo::setAttribute() expects 5|6|7, 3 given.', $errors[2]->getMessage());
		$this->assertSame(76, $errors[2]->getLine());
		$this->assertSame('Parameter #2 $val of method Bug7094\Foo::setAttribute() expects string, int given.', $errors[3]->getMessage());
		$this->assertSame(78, $errors[3]->getLine());
		$this->assertSame('Return type of call to method Bug7094\Foo::getAttribute() contains unresolvable type.', $errors[4]->getMessage());
		$this->assertSame(79, $errors[4]->getLine());

		$this->assertSame('Parameter #1 $attr of method Bug7094\Foo::setAttributes() expects array{foo?: string, bar?: 5|6|7, baz?: bool}, non-empty-array<\'bar\'|\'baz\'|\'foo\'|K of string, 5|6|7|bool|string> given.', $errors[5]->getMessage());
		$this->assertSame(29, $errors[5]->getLine());
	}

	#[RequiresPhp('>= 8.0')]
	public function testOffsetAccess(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/nsrt/offset-access.php');
		$this->assertCount(1, $errors);
		$this->assertSame('PHPDoc tag @return contains unresolvable type.', $errors[0]->getMessage());
		$this->assertSame(42, $errors[0]->getLine());
	}

	public function testUnresolvableParameter(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/unresolvable-parameter.php');
		$this->assertCount(3, $errors);
		$this->assertSame('Parameter #2 $array of function array_map expects array, list<string>|false given.', $errors[0]->getMessage());
		$this->assertSame(18, $errors[0]->getLine());
		$this->assertSame('Method UnresolvableParameter\Collection::pipeInto() has parameter $class with no type specified.', $errors[1]->getMessage());
		$this->assertSame(30, $errors[1]->getLine());
		$this->assertSame('PHPDoc tag @param for parameter $class contains unresolvable type.', $errors[2]->getMessage());
		$this->assertSame(30, $errors[2]->getLine());
	}

	public function testBug7248(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-7248.php');
		$this->assertNoErrors($errors);
	}

	public function testBug7351(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-7351.php');
		$this->assertNoErrors($errors);
	}

	public function testBug7381(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-7381.php');
		$this->assertNoErrors($errors);
	}

	public function testBug7153(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/nsrt/bug-7153.php');
		$this->assertNoErrors($errors);
	}

	public function testBug7275(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-7275.php');
		$this->assertNoErrors($errors);
	}

	public function testBug7500(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-7500.php');
		$this->assertNoErrors($errors);
	}

	public function testBug12767(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-12767.php');
		$this->assertCount(3, $errors);

		$this->assertSame('Expected type int, actual: *ERROR*', $errors[0]->getMessage());
		$this->assertSame('Undefined variable: $field1', $errors[1]->getMessage());
		$this->assertSame('Undefined variable: $field2', $errors[2]->getMessage());
	}

	public function testBug7554(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-7554.php');
		$this->assertCount(2, $errors);

		$this->assertSame(sprintf('Parameter #1 $%s of function count expects array|Countable, list<array<int, int<0, max>|string>>|false given.', PHP_VERSION_ID < 80000 ? 'var' : 'value'), $errors[0]->getMessage());
		$this->assertSame(26, $errors[0]->getLine());

		$this->assertSame('Cannot access offset int<1, max> on list<array{non-empty-string, int<0, max>}>|false.', $errors[1]->getMessage());
		$this->assertSame(27, $errors[1]->getLine());
	}

	public function testBug7637(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-7637.php');
		$this->assertCount(3, $errors);

		$this->assertSame('Method Bug7637\HelloWorld::getProperty() has invalid return type Bug7637\rex_backend_login.', $errors[0]->getMessage());
		$this->assertSame(54, $errors[0]->getLine());

		$this->assertSame('Method Bug7637\HelloWorld::getProperty() has invalid return type Bug7637\rex_timer.', $errors[1]->getMessage());
		$this->assertSame(54, $errors[1]->getLine());

		$this->assertSame('Call to function is_string() with string will always evaluate to true.', $errors[2]->getMessage());
		$this->assertSame(57, $errors[2]->getLine());
	}

	public function testBug12671(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-12671.php');
		$this->assertNoErrors($errors);
	}

	public function testBug7737(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-7737.php');
		$this->assertNoErrors($errors);
	}

	public function testBug7762(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-7762.php');
		$this->assertCount(2, $errors);
		$this->assertSame('Function json_decode invoked with 0 parameters, 1-4 required.', $errors[0]->getMessage());
		$this->assertSame('Function json_encode invoked with 0 parameters, 1-3 required.', $errors[1]->getMessage());
	}

	public function testPrestashopInfiniteRunXmlLoaderBug(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/prestashop-xml-loader.php');
		$this->assertCount(4, $errors);
		$this->assertSame('Property PrestaShopBundleInfiniteRunBug\XmlLoader::$data_path has no type specified.', $errors[0]->getMessage());
		$this->assertSame('Method PrestaShopBundleInfiniteRunBug\XmlLoader::getEntityInfo() has no return type specified.', $errors[1]->getMessage());
		$this->assertSame('Method PrestaShopBundleInfiniteRunBug\XmlLoader::getEntityInfo() has parameter $entity with no type specified.', $errors[2]->getMessage());
		$this->assertSame('Method PrestaShopBundleInfiniteRunBug\XmlLoader::getEntityInfo() has parameter $exists with no type specified.', $errors[3]->getMessage());
	}

	public function testBug7320(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-7320.php');
		$this->assertCount(1, $errors);
		$this->assertSame('Parameter #1 $c of function Bug7320\foo expects callable(int=): void, Closure(int): void given.', $errors[0]->getMessage());
		$this->assertSame(13, $errors[0]->getLine());
	}

	public function testBug7581(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-7581.php');
		$this->assertNoErrors($errors);
	}

	public function testBug7903(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-7903.php');
		$this->assertNoErrors($errors);
	}

	public function testBug7901(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-7901.php');
		$this->assertNoErrors($errors);
	}

	public function testBug7918(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-7918.php');
		$this->assertNoErrors($errors);
	}

	public function testBug7140(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-7140.php');
		$this->assertNoErrors($errors);
	}

	public function testArrayUnion(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/array-union.php');
		$this->assertNoErrors($errors);
	}

	public function testBug6948(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-6948.php');
		$this->assertNoErrors($errors);
	}

	public function testBug7963(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-7963.php');
		$this->assertNoErrors($errors);
	}

	public function testBug7963Two(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-7963-two.php');
		$this->assertNoErrors($errors);
	}

	#[RequiresPhp('>= 8.1')]
	public function testBug8078(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-8078.php');
		$this->assertNoErrors($errors);
	}

	#[RequiresPhp('>= 8.1')]
	public function testBug8072(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-8072.php');
		$this->assertNoErrors($errors);
	}

	public function testBug7787(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-7787.php');
		$this->assertCount(1, $errors);
		$this->assertSame('Reflection error: Circular reference to class "Bug7787\TestClass"', $errors[0]->getMessage());
	}

	public function testBug3865(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-3865.php');
		$this->assertCount(1, $errors);
		$this->assertSame('The @extends tag of class Bug3865\RecursiveClass describes Bug3865\RecursiveClass but the class extends Bug3865\EntityRepository.', $errors[0]->getMessage());
		$this->assertSame(14, $errors[0]->getLine());
	}

	public function testBug5312(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-5312.php');
		$this->assertCount(3, $errors);
		$this->assertSame('Parameter $object of method Bug5312\Updatable::update() has invalid type Bug5312\T.', $errors[0]->getMessage());
		$this->assertSame(13, $errors[0]->getLine());
		$this->assertSame('Type Bug5312\T in generic type Bug5312\Updatable<Bug5312\T> in PHPDoc tag @param for parameter $object is not subtype of template type T of Bug5312\Updatable<Bug5312\T> of interface Bug5312\Updatable.', $errors[1]->getMessage());
		$this->assertSame(13, $errors[1]->getLine());
		$this->assertSame('Type Bug5312\T in generic type Bug5312\Updatable<Bug5312\T> in PHPDoc tag @param for parameter $object is not subtype of template type T of Bug5312\Updatable<Bug5312\T> of interface Bug5312\Updatable.', $errors[2]->getMessage());
		$this->assertSame(13, $errors[2]->getLine());
	}

	public function testBug5390(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-5390.php');
		$this->assertCount(3, $errors);
		$this->assertSame('Property Bug5390\A::$b is never written, only read.', $errors[0]->getMessage());
		$this->assertSame(9, $errors[0]->getLine());
		$this->assertSame('Method Bug5390\A::infiniteRecursion() has no return type specified.', $errors[1]->getMessage());
		$this->assertSame(11, $errors[1]->getLine());
		$this->assertSame('Call to an undefined method Bug5390\B::someMethod().', $errors[2]->getMessage());
		$this->assertSame(12, $errors[2]->getLine());
	}

	public function testBug7110(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-7110.php');
		$this->assertCount(1, $errors);
		$this->assertSame('Parameter #1 $s of function Bug7110\takesInt expects int, string given.', $errors[0]->getMessage());
		$this->assertSame(34, $errors[0]->getLine());
	}

	public function testBug8376(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-8376.php');
		$this->assertNoErrors($errors);
	}

	#[RequiresPhp('>= 8.0')]
	public function testAssertDocblock(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/nsrt/assert-docblock.php');
		$this->assertCount(4, $errors);
		$this->assertSame('Call to method AssertDocblock\A::testInt() with string will always evaluate to false.', $errors[0]->getMessage());
		$this->assertSame(218, $errors[0]->getLine());
		$this->assertSame('Call to method AssertDocblock\A::testNotInt() with string will always evaluate to true.', $errors[1]->getMessage());
		$this->assertSame(224, $errors[1]->getLine());
		$this->assertSame('Call to method AssertDocblock\A::testInt() with int will always evaluate to true.', $errors[2]->getMessage());
		$this->assertSame(232, $errors[2]->getLine());
		$this->assertSame('Call to method AssertDocblock\A::testNotInt() with int will always evaluate to false.', $errors[3]->getMessage());
		$this->assertSame(238, $errors[3]->getLine());
	}

	#[RequiresPhp('>= 8.0')]
	public function testBug8147(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-8147.php');
		$this->assertNoErrors($errors);
	}

	#[RequiresPhp('>= 8.0')]
	public function testBug12934(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-12934.php');
		$this->assertNoErrors($errors);
	}

	public function testConditionalExpressionInfiniteLoop(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/conditional-expression-infinite-loop.php');
		$this->assertNoErrors($errors);
	}

	public function testPr2030(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/pr-2030.php');
		$this->assertNoErrors($errors);
	}

	public function testBug6265(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-6265.php');
		$this->assertNotEmpty($errors);
	}

	public function testBug8503(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-8503.php');
		$this->assertNoErrors($errors);
	}

	#[RequiresPhp('>= 8.0')]
	public function testBug8537(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-8537.php');
		$this->assertNoErrors($errors);
	}

	public function testBug8146(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-8146b.php');
		$this->assertNoErrors($errors);
	}

	public function testBug8215(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-8215.php');
		$this->assertNoErrors($errors);
	}

	public function testBug8146a(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-8146a.php');
		$this->assertNoErrors($errors);
	}

	public static function getAdditionalConfigFiles(): array
	{
		return [
			__DIR__ . '/../../../conf/bleedingEdge.neon',
		];
	}

	public function testBug8004(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-8004.php');
		$this->assertCount(2, $errors);
		$this->assertSame('Strict comparison using !== between null and DateTimeInterface|string will always evaluate to true.', $errors[0]->getMessage());
		$this->assertSame(49, $errors[0]->getLine());

		$this->assertSame('Strict comparison using !== between null and DateTimeInterface|string will always evaluate to true.', $errors[1]->getMessage());
		$this->assertSame(59, $errors[1]->getLine());
	}

	public function testSkipCheckNoGenericClasses(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/skip-check-no-generic-classes.php');
		$this->assertCount(1, $errors);
		$this->assertSame('Method SkipCheckNoGenericClasses\Foo::doFoo() has parameter $i with generic class LimitIterator but does not specify its types: TKey, TValue, TIterator', $errors[0]->getMessage());
	}

	#[RequiresPhp('>= 8.1')]
	public function testBug8983(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-8983.php');
		$this->assertNoErrors($errors);
	}

	public function testBug9008(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-9008.php');
		$this->assertNoErrors($errors);
	}

	public function testBug5091(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-5091.php');
		$this->assertNoErrors($errors);
	}

	public function testBug9459(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-9459.php');
		$this->assertCount(1, $errors);
		$this->assertSame('PHPDoc tag @var with type callable(): array<mixed> is not subtype of native type Closure(): array{}.', $errors[0]->getMessage());
	}

	public function testBug9573(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-9573.php');
		$this->assertNoErrors($errors);
	}

	public function testBug9039(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-9039.php');
		$this->assertCount(1, $errors);
		$this->assertSame('Constant Bug9039\Test::RULES is unused.', $errors[0]->getMessage());
	}

	#[RequiresPhp('>= 8.0')]
	public function testDiscussion9053(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/discussion-9053.php');
		$this->assertNoErrors($errors);
	}

	public function testProcessCalledMethodInfiniteLoop(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/process-called-method-infinite-loop.php');
		$this->assertNoErrors($errors);
	}

	#[RequiresPhp('>= 8.0')]
	public function testBug9428(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-9428.php');
		$this->assertNoErrors($errors);
	}

	public function testBug9690(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-9690.php');
		$this->assertNoErrors($errors);
	}

	public function testIgnoreIdentifiers(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/ignore-identifiers.php');
		$this->assertCount(5, $errors);

		$this->assertSame('No error with identifier wrong.id is reported on line 12.', $errors[0]->getMessage());
		$this->assertSame(12, $errors[0]->getLine());

		$this->assertSame('Undefined variable: $foo', $errors[1]->getMessage());
		$this->assertSame(12, $errors[1]->getLine());

		$this->assertSame('Undefined variable: $bar', $errors[2]->getMessage());
		$this->assertSame(14, $errors[2]->getLine());

		$this->assertSame('Undefined variable: $foo', $errors[3]->getMessage());
		$this->assertSame(14, $errors[3]->getLine());

		$this->assertSame('Undefined variable: $bar', $errors[4]->getMessage());
		$this->assertSame(16, $errors[4]->getLine());
	}

	#[RequiresPhp('>= 8.1')]
	public function testBug9994(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-9994.php');
		$this->assertCount(2, $errors);
		$this->assertSame('Negated boolean expression is always false.', $errors[0]->getMessage());
		$this->assertSame('Parameter #2 $callback of function array_filter expects (callable(1|2|3|null): bool)|null, false given.', $errors[1]->getMessage());
	}

	#[RequiresPhp('>= 8.1')]
	public function testBug10049(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-10049-recursive.php');
		$this->assertNoErrors($errors);
	}

	#[RequiresPhp('>= 8.0')]
	public function testBug10086(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-10086.php');
		$this->assertNoErrors($errors);
	}

	public function testBug10147(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-10147.php');
		$this->assertNoErrors($errors);
	}

	#[RequiresPhp('>= 8.2')]
	public function testBug10302(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-10302.php');
		$this->assertNoErrors($errors);
	}

	public function testBug10358(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-10358.php');
		$this->assertCount(1, $errors);
		$this->assertSame('Cannot use Ns\Foo2 as Foo because the name is already in use', $errors[0]->getMessage());
		$this->assertSame(6, $errors[0]->getLine());
	}

	public function testBug10509(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-10509.php');
		$this->assertCount(2, $errors);
		$this->assertSame('Method Bug10509\Foo::doFoo() has no return type specified.', $errors[0]->getMessage());
		$this->assertSame('PHPDoc tag @return contains unresolvable type.', $errors[1]->getMessage());
	}

	public function testBug10538(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-10538.php');
		$this->assertNoErrors($errors);
	}

	#[RequiresPhp('>= 8.1')]
	public function testBug10847(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-10847.php');
		$this->assertNoErrors($errors);
	}

	#[RequiresPhp('>= 8.1')]
	public function testBug10772(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-10772.php');
		$this->assertNoErrors($errors);
	}

	#[RequiresPhp('>= 8.1')]
	public function testBug10985(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-10985.php');
		$this->assertNoErrors($errors);
	}

	#[RequiresPhp('>= 8.1')]
	public function testBug10979(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-10979.php');
		$this->assertNoErrors($errors);
	}

	public function testBug11026(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-11026.php');
		$this->assertNoErrors($errors);
	}

	public function testBug10867(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-10867.php');
		$this->assertNoErrors($errors);
	}

	#[RequiresPhp('>= 8.1')]
	public function testBug11263(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-11263.php');
		$this->assertNoErrors($errors);
	}

	#[RequiresPhp('>= 8.0')]
	public function testBug11147(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-11147.php');
		$this->assertCount(1, $errors);
		$this->assertSame('Method Bug11147\RedisAdapter::createConnection() has invalid return type Bug11147\NonExistentClass.', $errors[0]->getMessage());
	}

	#[RequiresPhp('>= 8.0')]
	public function testBug11283(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-11283.php');
		$this->assertNoErrors($errors);
	}

	public function testBug11292(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-11292.php');
		$this->assertNoErrors($errors);
	}

	#[RequiresPhp('>= 8.1')]
	public function testBug11297(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-11297.php');
		$this->assertNoErrors($errors);
	}

	#[RequiresPhp('>= 8.0')]
	public function testBug5597(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-5597.php');
		$this->assertNoErrors($errors);
	}

	#[RequiresPhp('>= 8.0')]
	public function testBug11511(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-11511.php');
		$this->assertCount(1, $errors);
		$this->assertSame('Access to an undefined property object::$bar.', $errors[0]->getMessage());
	}

	public function testBug12214(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-12214.php');
		$this->assertNoErrors($errors);
	}

	public function testBug11640(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-11640.php');
		$this->assertNoErrors($errors);
	}

	#[RequiresPhp('>= 8.0')]
	public function testBug11709(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-11709.php');
		$this->assertNoErrors($errors);
	}

	public function testBug11913(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-11913.php');
		$this->assertNoErrors($errors);
	}

	#[RequiresPhp('>= 8.3')]
	public function testBug12549(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-12549.php');
		$this->assertNoErrors($errors);
	}

	public function testBug12627(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-12627.php');
		$this->assertNoErrors($errors);
	}

	#[RequiresPhp('>= 8.3')]
	public function testBug12159(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-12159.php');
		$this->assertNoErrors($errors);
	}

	public function testBug12787(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-12787.php');
		$this->assertNoErrors($errors);
	}

	public function testBug12800(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-12800.php');
		$this->assertNoErrors($errors);
	}

	#[RequiresPhp('>= 8.3')]
	public function testBug12949(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-12949.php');
		$this->assertCount(3, $errors);
		$this->assertSame('Call to an undefined method object::0().', $errors[0]->getMessage());
		$this->assertSame('Call to an undefined static method object::0().', $errors[1]->getMessage());
		$this->assertSame('Access to undefined constant object::0.', $errors[2]->getMessage());
	}

	public function testBug12979(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-12979.php');
		$this->assertNoErrors($errors);
	}

	public function testBug12095(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-12095.php');
		$this->assertNoErrors($errors);
	}

	public function testBug13279(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-13279.php');
		$this->assertCount(1, $errors);
		$this->assertSame('Parameter #2 $offset of function array_splice expects int, string given.', $errors[0]->getMessage());
	}

	public function testBug13310(): void
	{
		// require file to make sure the defined function is known
		require_once __DIR__ . '/data/bug-13310.php';

		$errors = $this->runAnalyse(__DIR__ . '/data/bug-13310.php');
		$this->assertNoErrors($errors);
	}

	public function testBug11912(): void
	{
		$errors = $this->runAnalyse(__DIR__ . '/data/bug-11912.php');
		$this->assertNoErrors($errors);
	}

	/**
	 * @param string[]|null $allAnalysedFiles
	 * @return Error[]
	 */
	private function runAnalyse(string $file, ?array $allAnalysedFiles = null): array
	{
		$file = $this->getFileHelper()->normalizePath($file);

		$analyser = self::getContainer()->getByType(Analyser::class);
		$finalizer = self::getContainer()->getByType(AnalyserResultFinalizer::class);
		$errors = $finalizer->finalize(
			$analyser->analyse([$file], null, null, true, $allAnalysedFiles),
			false,
			true,
		)->getErrors();
		foreach ($errors as $error) {
			$this->assertSame($file, $error->getFilePath());
		}

		return $errors;
	}

}
