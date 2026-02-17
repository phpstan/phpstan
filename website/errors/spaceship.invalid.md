---
title: "spaceship.invalid"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function compare(int $number, object $item): int
{
	return $number <=> $item;
}
```

## Why is it reported?

The spaceship operator (`<=>`) is used to compare two values, but the types of the operands are not compatible for comparison. PHP cannot meaningfully compare a numeric type against an object or array using this operator, and it results in an error.

## How to fix it

Make sure both sides of the spaceship operator are of compatible types.

```diff-php
 <?php declare(strict_types = 1);

-function compare(int $number, object $item): int
+function compare(int $number, int $other): int
 {
-	return $number <=> $item;
+	return $number <=> $other;
 }
```
