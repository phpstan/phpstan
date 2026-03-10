---
title: "conditionalType.subjectNotFound"
shortDescription: "Conditional return type subject does not reference a template or parameter."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @return (int is string ? true : false)
 */
function doFoo(): bool
{
	return true;
}
```

## Why is it reported?

A conditional return type uses a subject type (`int`) that does not reference any `@template` tag or function parameter. The subject of a conditional return type must be either a template type declared via `@template` or a parameter reference using the `$param is Type` syntax.

In the example above, `int` is a bare type name that is not declared as a template type, so PHPStan cannot evaluate the condition.

## How to fix it

If the condition depends on a parameter, use the `$param is Type` syntax:

```diff-php
 <?php declare(strict_types = 1);

 /**
+ * @param string|int $value
- * @return (int is string ? true : false)
+ * @return ($value is string ? true : false)
  */
-function doFoo(): bool
+function doFoo($value): bool
 {
-	return true;
+	return is_string($value);
 }
```

If you intend to use a template type as the subject, declare it with `@template`:

```diff-php
 <?php declare(strict_types = 1);

 /**
+ * @template T
- * @return (int is string ? true : false)
+ * @param T $value
+ * @return (T is string ? true : false)
  */
-function doFoo(): bool
+function doFoo($value): bool
 {
-	return true;
+	return is_string($value);
 }
```
