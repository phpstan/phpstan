---
title: "sealed.deprecatedTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewTrait instead */
trait DeprecatedTrait {}

/**
 * @phpstan-sealed(AllowedClass)
 * @phpstan-require-extends SomeBase
 */
class MyClass
{
    /** @deprecated */
    use DeprecatedTrait;
}
```

## Why is it reported?

The `@phpstan-sealed` PHPDoc tag references a trait that has been marked as `@deprecated`. Deprecated symbols are scheduled for removal or replacement. Using a deprecated trait in a `@phpstan-sealed` declaration ties the sealed constraint to an API that will eventually be removed.

## How to fix it

Replace the deprecated trait reference with its non-deprecated replacement:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @phpstan-sealed(DeprecatedTrait)
+ * @phpstan-sealed(NewTrait)
  */
 class MyClass
 {
-    use DeprecatedTrait;
+    use NewTrait;
 }
```

Or remove the `@phpstan-sealed` tag if the constraint is no longer needed.

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.
