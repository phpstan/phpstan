---
title: "ternary.condNotBoolean"
ignorable: true
---

This error is reported by `phpstan/phpstan-strict-rules`.

## Code example

```php
<?php declare(strict_types = 1);

function getLabel(?string $name): string
{
	return $name ? $name : 'anonymous';
}
```

## Why is it reported?

The condition of the ternary operator (`? :`) is expected to be a boolean value when `phpstan/phpstan-strict-rules` is enabled. Using non-boolean values like strings, integers, or nullable types in boolean contexts can lead to subtle bugs because of PHP's loose type coercion rules (e.g., `0`, `""`, and `"0"` are all falsy).

## How to fix it

Use an explicit boolean expression in the ternary condition.

```diff-php
 <?php declare(strict_types = 1);

 function getLabel(?string $name): string
 {
-	return $name ? $name : 'anonymous';
+	return $name !== null ? $name : 'anonymous';
 }
```
