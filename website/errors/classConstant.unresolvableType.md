---
title: "classConstant.unresolvableType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Config
{
	/** @var int&string */
	const DEFAULT_TIMEOUT = 30;
}
```

## Why is it reported?

The `@var` PHPDoc tag on a class constant contains a type that cannot be resolved. This typically happens when the type is an impossible intersection (like `int&string` which can never exist), references a non-existent class, or uses invalid type syntax.

In the example above, `int&string` is an intersection type that can never be satisfied because no value can be both an `int` and a `string` at the same time.

## How to fix it

Correct the `@var` PHPDoc type to use a valid, resolvable type:

```diff-php
 class Config
 {
-	/** @var int&string */
+	/** @var int */
 	const DEFAULT_TIMEOUT = 30;
 }
```

On PHP 8.3 and later, native typed constants can be used instead of PHPDoc:

```php
<?php declare(strict_types = 1);

class Config
{
	const int DEFAULT_TIMEOUT = 30;
}
```
