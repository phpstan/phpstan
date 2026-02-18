---
title: "selfOut.static"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @template T
 */
class Collection
{
	/**
	 * @phpstan-self-out self<int>
	 */
	public static function createIntCollection(): void
	{
	}
}
```

## Why is it reported?

The `@phpstan-self-out` PHPDoc tag is placed on a static method. This tag is used to narrow the type of `$this` after a method call, allowing PHPStan to track how the object's generic type changes through a fluent interface. Since static methods do not operate on an instance (`$this` is not available), the `@phpstan-self-out` tag has no meaning on static methods.

## How to fix it

If the method needs to use `@phpstan-self-out`, make it non-static:

```diff-php
 /**
  * @phpstan-self-out self<int>
  */
-public static function createIntCollection(): void
+public function setIntType(): void
 {
 }
```

If the method must remain static, remove the `@phpstan-self-out` tag and use a return type instead:

```diff-php
-/**
- * @phpstan-self-out self<int>
- */
-public static function createIntCollection(): void
+/**
+ * @return self<int>
+ */
+public static function createIntCollection(): self
 {
+	return new self();
 }
```
