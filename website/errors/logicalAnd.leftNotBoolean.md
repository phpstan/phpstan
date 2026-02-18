---
title: "logicalAnd.leftNotBoolean"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(string $s, bool $b): void
{
	$result = $s and $b;
}
```

## Why is it reported?

This error comes from [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules). It reports that the left side of an `and` operator is not a boolean value. Strict boolean comparisons require both sides to be of type `bool` to avoid subtle bugs caused by PHP's type juggling. In the example, `$s` is a `string`, which will be implicitly coerced to `bool`.

## How to fix it

Explicitly convert the value to a boolean:

```diff-php
 function doFoo(string $s, bool $b): void
 {
-	$result = $s and $b;
+	$result = ($s !== '') and $b;
 }
```

Or use a boolean variable:

```diff-php
-function doFoo(string $s, bool $b): void
+function doFoo(bool $a, bool $b): void
 {
-	$result = $s and $b;
+	$result = $a and $b;
 }
```
