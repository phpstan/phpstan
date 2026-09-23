---
title: "preInc.overwritten"
shortDescription: "The result of a pre-increment (++$i) is overwritten before it is ever read."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
	$i = 0;
	++$i;
	$i = 5;
	echo $i;
}
```

## Why is it reported?

The pre-increment `++$i` raises `$i` by one, but that new value is never read — `$i = 5` replaces it before any code looks at `$i`. The pre-increment has no effect on the program. This usually indicates leftover code, or a logic error where the incremented value was meant to be used before the variable is reassigned.

This is different from [`preInc.unused`](/error-identifiers/preInc.unused), where the new value is simply never read. Here another assignment overwrites it first.

This rule is part of PHPStan's dead code analysis. It is reported at [rule level](/user-guide/rule-levels) 4 and above, and is currently part of [Bleeding Edge](/blog/what-is-bleeding-edge).

## How to fix it

Remove the pre-increment if the new value is not needed:

```diff-php
 function doFoo(): void
 {
 	$i = 0;
-	++$i;
 	$i = 5;
 	echo $i;
 }
```

Or use the new value before the variable is reassigned:

```diff-php
 function doFoo(): void
 {
 	$i = 0;
 	++$i;
+	echo $i;
 	$i = 5;
 	echo $i;
 }
```
