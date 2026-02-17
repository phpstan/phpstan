---
title: "equal.notAllowed"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function check(int $a, int $b): bool
{
	return $a == $b;
}
```

## Why is it reported?

This error is reported by `phpstan/phpstan-strict-rules`.

Loose comparison via `==` is not allowed. The `==` operator performs type juggling before comparing values, which can lead to unexpected results. For example, `0 == 'foo'` evaluates to `true` in PHP versions before 8.0, and `'' == null` is `true` in all versions.

Using strict comparison (`===`) avoids these pitfalls by comparing both value and type.

## How to fix it

Replace the loose comparison operator with its strict equivalent:

```diff-php
 <?php declare(strict_types = 1);

 function check(int $a, int $b): bool
 {
-	return $a == $b;
+	return $a === $b;
 }
```
