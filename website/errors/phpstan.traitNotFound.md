---
title: "phpstan.traitNotFound"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App\Tests;

trait MyTestHelperTrait
{
	public function helper(): void
	{
		// ...
	}
}
```

## Why is it reported?

PHPStan found a trait definition in the analysed code, but the trait is not registered in the reflection provider. This typically happens when test traits are not autoloaded because the `autoload-dev` section in `composer.json` does not include the directory containing the trait.

This error is not ignorable because PHPStan cannot analyse code that uses a trait it cannot find.

## How to fix it

Configure the `autoload-dev` section in `composer.json` to include the directory where the test files are located:

```diff-php
 {
     "autoload-dev": {
         "psr-4": {
-            "App\\Tests\\": "tests/"
+            "App\\Tests\\": "tests/",
+            "App\\Tests\\Helpers\\": "tests/Helpers/"
         }
     }
 }
```

After updating `composer.json`, run `composer dump-autoload` to regenerate the autoloader.
