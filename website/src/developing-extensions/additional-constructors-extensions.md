---
title: Additional Constructors
---

When [`checkUninitializedProperties`](/config-reference#checkuninitializedproperties) is enabled, PHPStan reports properties with native types that weren't initialized in the class constructor. But sometimes properties are initialized in other methods that act as constructors — like `setUp()` in PHPUnit test cases, or dependency injection setter methods.

<div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">

For simple cases, you can use the [`additionalConstructors`](/config-reference#additionalconstructors) configuration parameter without writing an extension:

```yaml
parameters:
	additionalConstructors:
		- PHPUnit\Framework\TestCase::setUp
```

The extension is useful when you need dynamic logic — for example, marking a method as a constructor for all subclasses of a certain class.

</div>

This is [the interface](https://apiref.phpstan.org/__BRANCH__/PHPStan.Reflection.AdditionalConstructorsExtension.html) your extension needs to implement:

```php
namespace PHPStan\Reflection;

interface AdditionalConstructorsExtension
{

	/** @return string[] */
	public function getAdditionalConstructors(ClassReflection $classReflection): array;

}
```

The `getAdditionalConstructors()` method receives a class reflection and returns an array of method names that should be treated as constructors for that class. Return an empty array when the extension doesn't apply to the given class.

Here's an example that marks `setUp()` as a constructor for all PHPUnit test cases:

```php
namespace App\PHPStan;

use PHPStan\Reflection\AdditionalConstructorsExtension;
use PHPStan\Reflection\ClassReflection;

class TestCaseAdditionalConstructorsExtension implements AdditionalConstructorsExtension
{

	public function getAdditionalConstructors(ClassReflection $classReflection): array
	{
		if ($classReflection->is(\PHPUnit\Framework\TestCase::class)) {
			return ['setUp'];
		}

		return [];
	}

}
```

With this extension, PHPStan no longer reports uninitialized properties that are assigned in `setUp()`:

```php
class MyTest extends \PHPUnit\Framework\TestCase
{
	private Connection $connection; // No error - setUp is treated as a constructor

	protected function setUp(): void
	{
		$this->connection = new Connection();
	}
}
```

The implementation needs to be registered in your [configuration file](/config-reference):

```yaml
services:
	-
		class: App\PHPStan\TestCaseAdditionalConstructorsExtension
		tags:
			- phpstan.additionalConstructorsExtension
```
