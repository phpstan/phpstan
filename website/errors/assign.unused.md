---
title: "assign.unused"
shortDescription: "A value assigned to a variable is overwritten before it is ever read."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): int
{
	$a = 1;
	$a = 2;
	return $a;
}
```

## Why is it reported?

The value `1` assigned to `$a` is never read — the variable is overwritten by `$a = 2` before its first value is ever used. This is a dead store: the assignment computes a value that no code observes.

Unlike [`variable.unused`](/error-identifiers/variable.unused), the variable itself *is* used later; only this particular assignment is redundant. Dead stores often point to a logic error, such as forgetting to use the first value or an accidental overwrite.

This rule is part of PHPStan's dead code analysis. It is reported at [rule level](/user-guide/rule-levels) 4 and above, and is currently part of [Bleeding Edge](/blog/what-is-bleeding-edge).

## How to fix it

Remove the assignment whose value is never read:

```diff-php
 function doFoo(): int
 {
-	$a = 1;
 	$a = 2;
 	return $a;
 }
```

Or, if the first value should have been used, fix the logic so it is read before being overwritten:

```diff-php
 function doFoo(): int
 {
 	$a = 1;
+	echo $a;
 	$a = 2;
 	return $a;
 }
```
