---
title: "argument.unpackInPartialApplication"
shortDescription: "Argument unpacking with ... is combined with placeholders in a partial function application."
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $a, int $b, int $c): int
{
	return $a + $b + $c;
}

/** @param array<int> $ints */
function test(array $ints): void
{
	$f = doFoo(?, ...$ints);
}
```

## Why is it reported?

A [partial function application](https://wiki.php.net/rfc/partial_function_application_v2) (PHP 8.6+) needs to know at compile time which parameters are bound and which are left open by `?` placeholders. Argument unpacking (`...$args`) supplies an unknown number of arguments, which makes that impossible. PHP rejects this combination with a compile-time error, so the file cannot be loaded at all.

## How to fix it

Pass the bound arguments individually instead of unpacking them:

```diff-php
 /** @param array<int> $ints */
 function test(array $ints): void
 {
-	$f = doFoo(?, ...$ints);
+	$f = doFoo(?, $ints[0], $ints[1]);
 }
```

Or write an explicit closure that does the unpacking:

```diff-php
 /** @param array<int> $ints */
 function test(array $ints): void
 {
-	$f = doFoo(?, ...$ints);
+	$f = static fn (int $a): int => doFoo($a, ...$ints);
 }
```
