---
title: Always-used class constants
---

PHPStan is [able to detect unused private class constants](/blog/detecting-unused-private-properties-methods-constants). There might be some cases where PHPStan thinks a class constant is unused, but the code might actually be correct. For example custom enum implementations like [Consistence enums](https://github.com/consistence-community/consistence/blob/66fcbc4710e3518b37f4b4e4133a6e504dc6650a/docs/Enum/enums.md) might take advantage of reflection to write and read private constants which static analysis cannot understand, but fortunately you can write a custom extension to make PHPStan understand what's going on and avoid false-positives.

The implementation is all about applying the [core concepts](/developing-extensions/core-concepts) like [reflection](/developing-extensions/reflection) so check out that guide first and then continue here.

This is [the interface](https://apiref.phpstan.org/2.0.x/PHPStan.Rules.Constants.AlwaysUsedClassConstantsExtension.html) your extension needs to implement:

```php
namespace PHPStan\Rules\Constants;

use PHPStan\Reflection\ConstantReflection;

interface AlwaysUsedClassConstantsExtension
{

	public function isAlwaysUsed(ConstantReflection $constant): bool;

}
```

The implementation needs to be registered in your [configuration file](/config-reference):

```yaml
services:
	-
		class: MyApp\PHPStan\ConstantsExtension
		tags:
			- phpstan.constants.alwaysUsedClassConstantsExtension
```
