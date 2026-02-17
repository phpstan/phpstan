---
title: "pipe.byRef"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function modify(string &$s): void
{
	$s = strtoupper($s);
}

$result = 'hello' |> modify(...);
```

## Why is it reported?

The pipe operator `|>` passes the left-hand side as the first argument to the callable on the right. This value is passed by value, not by reference. When the callable declares its first parameter as pass-by-reference (`&`), the pipe operator cannot fulfil that contract, so PHPStan reports an error.

## How to fix it

Change the callable to accept its first parameter by value instead of by reference, and return the result:

```diff-php
 <?php declare(strict_types = 1);

-function modify(string &$s): void
+function modify(string $s): string
 {
-	$s = strtoupper($s);
+	return strtoupper($s);
 }

 $result = 'hello' |> modify(...);
```
