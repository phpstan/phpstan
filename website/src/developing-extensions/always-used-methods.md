---
title: Always-used methods
---

PHPStan is [able to detect unused private class methods](/blog/detecting-unused-private-properties-methods-constants). There might be some cases where PHPStan thinks a class method is unused, but the code might actually be correct. For example, libraries might take advantage of reflection to write and read private methods which static analysis cannot understand, but fortunately you can write a custom extension to make PHPStan understand what's going on and avoid false-positives.

The implementation is all about applying the [core concepts](/developing-extensions/core-concepts) like [reflection](/developing-extensions/reflection) so check out that guide first and then continue here.

This is [the interface](https://apiref.phpstan.org/2.1.x/PHPStan.Rules.Methods.AlwaysUsedMethodExtension.html) your extension needs to implement:

```php
namespace PHPStan\Rules\Methods;

use PHPStan\Reflection\MethodReflection;

interface AlwaysUsedMethodExtension
{

	public function isAlwaysUsed(MethodReflection $methodReflection): bool;

}
```

The implementation needs to be registered in your [configuration file](/config-reference):

```yaml
services:
	-
		class: MyApp\PHPStan\MethodsExtension
		tags:
			- phpstan.methods.alwaysUsedMethodExtension
```
