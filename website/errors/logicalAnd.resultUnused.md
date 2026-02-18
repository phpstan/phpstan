---
title: "logicalAnd.resultUnused"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(bool $a, bool $b): void
{
	$result = $a and $b;
}
```

## Why is it reported?

The result of the `and` operator is unused. This is almost always a bug caused by the unexpected precedence of the `and` operator. The `and` operator has lower precedence than `=`, so `$result = $a and $b` is parsed as `($result = $a) and $b`. The variable `$result` receives the value of `$a`, and the `and $b` part is evaluated but its result is discarded.

## How to fix it

Use `&&` instead of `and`, which has higher precedence than `=`:

```diff-php
 function doFoo(bool $a, bool $b): void
 {
-	$result = $a and $b;
+	$result = $a && $b;
 }
```

Or use parentheses to disambiguate:

```diff-php
 function doFoo(bool $a, bool $b): void
 {
-	$result = $a and $b;
+	$result = ($a and $b);
 }
```
