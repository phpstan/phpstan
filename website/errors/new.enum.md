---
title: "new.enum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

enum Suit
{
	case Hearts;
	case Diamonds;
	case Clubs;
	case Spades;
}

$suit = new Suit(); // error: Cannot instantiate enum Suit.
```

## Why is it reported?

Enums in PHP cannot be instantiated using the `new` keyword. Enum cases are predefined singleton instances and must be accessed directly via their case names (e.g., `Suit::Hearts`). Attempting to use `new` on an enum will result in a fatal error at runtime.

## How to fix it

Use the enum case directly instead of trying to instantiate it.

```diff-php
-$suit = new Suit();
+$suit = Suit::Hearts;
```
