---
title: "classConstant.unresolvableType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	/** @var self&\stdClass */
	const FOO = 1;
}
```

## Why is it reported?

The `@var` PHPDoc tag on a class constant contains a type that cannot be resolved. In this example, the intersection type `self&\stdClass` creates a type that can never exist because `Foo` and `\stdClass` are unrelated classes, making the intersection unresolvable.

Common causes of unresolvable types in class constant PHPDoc include:
- Intersection types between incompatible classes
- References to non-existent classes or types
- Types that result in logical contradictions

## How to fix it

Correct the `@var` PHPDoc type to use a valid, resolvable type:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	/** @var self&\stdClass */
+	/** @var int */
 	const FOO = 1;
 }
```

On PHP 8.3 and later, native typed constants can be used instead of PHPDoc:

```php
<?php declare(strict_types = 1);

class Foo
{
	const int FOO = 1;
}
```
