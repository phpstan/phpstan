---
title: Custom Deprecations
---

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 2.1.12</div>

PHPStan allows you adjust behaviour of [reflection](/developing-extensions/reflection) methods `->isDeprecated()` and `->getDeprecationDescription()` to return custom deprecation information based on e.g. custom PHP attributes. 
This is useful when you want to mark a class, method, constant or property as deprecated in a way that PHPStan can understand.

There are several interfaces you can implement to achieve this:

- [`PHPStan\Reflection\Deprecation\ClassConstantDeprecationExtension`](https://apiref.phpstan.org/2.1.x/PHPStan.Reflection.Deprecation.ClassConstantDeprecationExtension.html)
- [`PHPStan\Reflection\Deprecation\ClassDeprecationExtension`](https://apiref.phpstan.org/2.1.x/PHPStan.Reflection.Deprecation.ClassDeprecationExtension.html)
- [`PHPStan\Reflection\Deprecation\ConstantDeprecationExtension`](https://apiref.phpstan.org/2.1.x/PHPStan.Reflection.Deprecation.ConstantDeprecationExtension.html)
- [`PHPStan\Reflection\Deprecation\EnumCaseDeprecationExtension`](https://apiref.phpstan.org/2.1.x/PHPStan.Reflection.Deprecation.EnumCaseDeprecationExtension.html)
- [`PHPStan\Reflection\Deprecation\FunctionDeprecationExtension`](https://apiref.phpstan.org/2.1.x/PHPStan.Reflection.Deprecation.FunctionDeprecationExtension.html)
- [`PHPStan\Reflection\Deprecation\MethodDeprecationExtension`](https://apiref.phpstan.org/2.1.x/PHPStan.Reflection.Deprecation.MethodDeprecationExtension.html)
- [`PHPStan\Reflection\Deprecation\PropertyDeprecationExtension`](https://apiref.phpstan.org/2.1.x/PHPStan.Reflection.Deprecation.PropertyDeprecationExtension.html)


For example, if you want to mark a class as deprecated using the `#[MyDeprecated]` attribute, you can implement the `ClassDeprecationExtension` interface like this:

```php

namespace App;

use App\MyDeprecated;
use PHPStan\Reflection\Deprecation\Deprecation;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionEnum;
use PHPStan\Reflection\Deprecation\ClassDeprecationExtension;

class CustomDeprecationExtension implements ClassDeprecationExtension
{

	/**
	 * @param ReflectionClass|ReflectionEnum $reflection
	 */
	public function getClassDeprecation($reflection): ?Deprecation
	{
		foreach ($reflection->getAttributes(MyDeprecated::class) as $attribute) {
			$description = $attribute->getArguments()[0] ?? $attribute->getArguments()['description'] ?? null;
			return $description === null
				? Deprecation::create()
				: Deprecation::createWithDescription($description);
		}

		return null;
	}
}
```

The implementation needs to be registered in your [configuration file](/config-reference):

```yaml
services:
	-
		class: App\CustomDeprecationExtension
		tags:
			- phpstan.classDeprecationExtension
```
