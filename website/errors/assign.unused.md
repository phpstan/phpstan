---
title: "assign.unused"
shortDescription: "A value assigned to a variable is never read afterwards."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
	$a = 1;
	echo $a;
	$a = 2;
}
```

## Why is it reported?

The value `2` assigned to `$a` is never read — no code after the assignment looks at `$a` again. This is a dead store: the assignment computes a value that no code observes.

Unlike [`variable.unused`](/error-identifiers/variable.unused), the variable itself *is* read elsewhere; only this particular assignment is redundant. Unlike [`assign.overwritten`](/error-identifiers/assign.overwritten), nothing replaces the value — it is simply never used. Dead stores often point to leftover code or a logic error, such as forgetting to use the new value.

This rule is part of PHPStan's dead code analysis. It is reported at [rule level](/user-guide/rule-levels) 4 and above, and is currently part of [Bleeding Edge](/blog/what-is-bleeding-edge).

## How to fix it

Remove the assignment whose value is never read:

```diff-php
 function doFoo(): void
 {
 	$a = 1;
 	echo $a;
-	$a = 2;
 }
```

Or use the assigned value if it was intended to be read:

```diff-php
 function doFoo(): void
 {
 	$a = 1;
 	echo $a;
 	$a = 2;
+	echo $a;
 }
```

If you deliberately want to keep the assignment, prefix the variable name with an underscore. PHPStan ignores variables whose name starts with `_`.
