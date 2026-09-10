---
title: "variable.unused"
shortDescription: "A variable is assigned but never read anywhere."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
	$a = 1;
}
```

## Why is it reported?

The variable `$a` is assigned a value but is never read on any code path. The assignment has no effect on the program's behaviour. This usually indicates a logic error, a typo in the variable name, or leftover code from a refactoring.

This rule is part of PHPStan's dead code analysis. It is reported at [rule level](/user-guide/rule-levels) 4 and above, and is currently part of [Bleeding Edge](/blog/what-is-bleeding-edge).

## How to fix it

Remove the unused variable:

```diff-php
 function doFoo(): void
 {
-	$a = 1;
 }
```

Or use the variable if the value was meant to be consumed:

```diff-php
 function doFoo(): void
 {
 	$a = 1;
+	echo $a;
 }
```

If the assignment is a deliberate placeholder that you want to keep, prefix the variable name with an underscore. PHPStan ignores variables whose name starts with `_`:

```diff-php
-	$a = 1;
+	$_a = 1;
```
