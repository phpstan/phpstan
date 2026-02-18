---
title: "enum.destructor"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1); // lint >= 8.1

enum Suit
{
	case Hearts;
	case Diamonds;

	public function __destruct()
	{
	}
}
```

## Why is it reported?

PHP enums cannot have destructors. Enum cases are singleton values managed by the engine and are not subject to normal object lifecycle. Defining `__destruct` in an enum is a compile-time error in PHP.

## How to fix it

Remove the destructor from the enum:

```diff-php
 enum Suit
 {
 	case Hearts;
 	case Diamonds;
-
-	public function __destruct()
-	{
-	}
 }
```

If cleanup logic is needed, move it to a separate class that uses the enum rather than defining it on the enum itself.
