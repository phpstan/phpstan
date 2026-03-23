---
title: "enum.caseOutsideOfEnum"
shortDescription: "Using an enum case declaration inside a class, interface, or trait instead of an enum."
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	case Active;
}
```

## Why is it reported?

The `case` keyword for declaring enum members is only valid inside `enum` declarations. Using it inside a regular class, interface, or trait is a syntax-level mistake. PHP enums were introduced in PHP 8.1, and the `case` keyword has special meaning only within an `enum` body.

This usually happens when the `enum` keyword was accidentally replaced with `class`, or the declaration was meant to be an enum but was written as a class or interface.

## How to fix it

Change the declaration to an enum:

```diff-php
-class Foo
+enum Foo
 {
 	case Active;
 }
```

If you need a backed enum with string or integer values:

```diff-php
-class Foo
+enum Foo: string
 {
-	case Active;
+	case Active = 'active';
 }
```
