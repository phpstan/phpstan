---
title: Testing
---

Your custom extensions should have unit tests in order to prevent bugs sneaking into your code.

PHPStan supports testing with the industry standard [PHPUnit](https://phpunit.de/) framework.

Custom rules
-----------------

[Custom rules](/developing-extensions/rules) can be tested in a test case extended from [`PHPStan\Testing\RuleTestCase`](https://apiref.phpstan.org/1.12.x/PHPStan.Testing.RuleTestCase.html). A typical test can look like this:

```php
<?php declare(strict_types = 1);

namespace App\PHPStan;

use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<MyRule>
 */
class MyRuleTest extends RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		// getRule() method needs to return an instance of the tested rule
		return new MyRule();
	}

	public function testRule(): void
	{
		// first argument: path to the example file that contains some errors that should be reported by MyRule
		// second argument: an array of expected errors,
		// each error consists of the asserted error message, and the asserted error file line
		$this->analyse([__DIR__ . '/data/my-rule.php'], [
			[
				'X should not be Y', // asserted error message
				15, // asserted error line
			],
		]);

		// the test fails, if the expected error does not occur,
		// or if there are other errors reported beside the expected one
	}

	public static function getAdditionalConfigFiles(): array
	{
		// path to your project's phpstan.neon, or extension.neon in case of custom extension packages
		// this is only necessary if your custom rule relies on some extra configuration and other extensions
		return [__DIR__ . '/../extension.neon'];
	}

}
```

Type inference
-----------------

Other extension types, like [dynamic return type extensions](/developing-extensions/dynamic-return-type-extensions) or [type-specifying extensions](/developing-extensions/type-specifying-extensions), do not report errors by themselves, but rather influence the inferred types by the analysis engine so that PHPStan understands the analysed code better, remove false-positives[^fp], and narrow down the types to report more useful errors.

[^fp]: An error that's reported but isn't actually a bug in the analysed code.

A typical type inference test case class might look like this:

```php
<?php declare(strict_types = 1);

namespace App\PHPStan;

use PHPStan\Testing\TypeInferenceTestCase;

class MyContainerDynamicReturnTypeExtensionTest extends TypeInferenceTestCase
{

	/**
	 * @return iterable<mixed>
	 */
	public static function dataFileAsserts(): iterable
	{
		// path to a file with actual asserts of expected types:
		yield from self::gatherAssertTypes(__DIR__ . '/data/my-container-types.php');
	}

	/**
	 * @dataProvider dataFileAsserts
	 */
	public function testFileAsserts(
		string $assertType,
		string $file,
		mixed ...$args
	): void
	{
		$this->assertFileAsserts($assertType, $file, ...$args);
	}

	public static function getAdditionalConfigFiles(): array
	{
		// path to your project's phpstan.neon, or extension.neon in case of custom extension packages
		return [__DIR__ . '/../extension.neon'];
	}

}

```

And the referenced file looks like normal analysed code, but takes advantage of the `\PHPStan\Testing\assertType()` function with the type assertions:

```php
<?php

namespace MyTypesTest;

use function PHPStan\Testing\assertType;

class Foo
{

	public function doFoo(\App\MyContainer $container): void
	{
		// arguments: string with the expected type, actual type
		assertType(FooService::class, $container->getService('foo'));
		assertType(BarService::class, $container->getService('bar'));

		assertType('bool', $container->getParameter('isProduction'));
	}

}
```
