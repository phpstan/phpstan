---
title: "classConstant.deprecatedTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewHelper instead */
trait DeprecatedHelper
{
	public const VERSION = '1.0';
}

class MyClass
{
	use DeprecatedHelper;
}

function doFoo(): void
{
	echo MyClass::VERSION;
}
```

## Why is it reported?

The class constant being accessed belongs to a trait that is marked as `@deprecated`. Accessing constants from deprecated traits indicates reliance on code that is scheduled for removal. This rule is part of the `phpstan-deprecation-rules` package and helps identify usages that should be migrated.

## How to fix it

Replace the deprecated constant access with the recommended alternative:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): void
 {
-	echo MyClass::VERSION;
+	echo NewHelper::VERSION;
 }
```
