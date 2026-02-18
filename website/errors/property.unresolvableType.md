---
title: "property.unresolvableType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class UserService
{
	/** @var string&int */
	private $identifier;
}
```

## Why is it reported?

The PHPDoc type for a class property contains a type that PHPStan cannot resolve. This typically happens when the type is an intersection of incompatible types (like `string&int` which no value can ever satisfy), references a misspelled or undefined class, or uses invalid type syntax.

In the example above, `string&int` is an intersection type that can never exist because no value can be both a `string` and an `int` at the same time.

## How to fix it

Correct the PHPDoc type so it references valid, compatible types:

```diff-php
 class UserService
 {
-	/** @var string&int */
+	/** @var string */
 	private $identifier;
 }
```

If the property should accept multiple types, use a union type instead of an intersection:

```diff-php
 class UserService
 {
-	/** @var string&int */
+	/** @var string|int */
 	private $identifier;
 }
```
