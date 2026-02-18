---
title: "enum.nameCase"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

enum Suit
{
	case Hearts;
	case Diamonds;
}

function doFoo(suit $s): void
{
}
```

## Why is it reported?

The enum name is referenced with incorrect letter casing. While PHP class and enum names are case-insensitive at runtime, using inconsistent casing leads to confusion and makes code harder to maintain. The reference `suit` does not match the declared name `Suit`.

## How to fix it

Use the correct casing that matches the enum declaration:

```diff-php
-function doFoo(suit $s): void
+function doFoo(Suit $s): void
 {
 }
```
