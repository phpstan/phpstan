---
title: "mixin.deprecatedClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewHelper instead */
class OldHelper
{
	public function help(): void {}
}

/**
 * @mixin OldHelper
 */
class Foo // ERROR: PHPDoc tag @mixin references deprecated class OldHelper.
{
}
```

## Why is it reported?

This error is reported by `phpstan/phpstan-deprecation-rules`.

The `@mixin` PHPDoc tag references a class that has been marked as deprecated with the `@deprecated` PHPDoc tag. Using deprecated classes should be avoided because they may be removed in a future version of the library or application. The deprecation notice typically suggests a replacement class to use instead.

## How to fix it

Replace the deprecated class with its suggested replacement:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @mixin OldHelper
+ * @mixin NewHelper
  */
 class Foo
 {
 }
```

If no direct replacement exists, remove the `@mixin` tag and implement the needed methods directly:

```diff-php
 <?php declare(strict_types = 1);

-/**
- * @mixin OldHelper
- */
 class Foo
 {
+	public function help(): void {}
 }
```
