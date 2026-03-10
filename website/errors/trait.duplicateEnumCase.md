---
title: "trait.duplicateEnumCase"
shortDescription: "Enum case with the same name is declared more than once in a trait."
ignorable: false
feasible: false
---

## Code example

```php
<?php declare(strict_types = 1);

trait MyTrait
{
	case Active;
	case Active;
}

class Foo
{
	use MyTrait;
}
```

## Why is it reported?

An enum case with the same name is declared more than once in a trait body. This is reported by a generic duplicate-declaration rule that checks all class-like structures uniformly. While enum cases inside a trait are not valid PHP, PHPStan still detects the duplicate declaration when the trait is used by a class.

## How to fix it

Remove the duplicate enum case or rename it:

```diff-php
 trait MyTrait
 {
 	case Active;
-	case Active;
+	case Inactive;
 }
```
