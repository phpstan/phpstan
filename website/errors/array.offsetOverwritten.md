---
title: "array.offsetOverwritten"
shortDescription: "A value stored under an array offset is overwritten before it is ever read."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @param mixed $v */
function sink($v): void
{
}

function doFoo(): void
{
	$a = ['x' => 1, 'y' => 2];
	$a['x'] = 3;
	sink($a);
}
```

## Why is it reported?

The array assigned to `$a` stores the value `1` under offset `'x'`, but `$a['x'] = 3` replaces it before any code reads it. The original value under `'x'` is a dead store: it is computed and kept, but no code observes it.

Unlike [`variable.unused`](/error-identifiers/variable.unused), the array `$a` itself *is* used; only this particular offset of the assigned array is redundant. Unlike [`array.unusedOffset`](/error-identifiers/array.unusedOffset), the offset's value is not just left unread — a later write overwrites it first. This often points to a leftover key or a logic error, such as writing to the wrong offset.

This rule is part of PHPStan's dead code analysis. It is reported at [rule level](/user-guide/rule-levels) 4 and above, and is currently part of [Bleeding Edge](/blog/what-is-bleeding-edge).

## How to fix it

Put the final value directly into the array literal:

```diff-php
 function doFoo(): void
 {
-	$a = ['x' => 1, 'y' => 2];
-	$a['x'] = 3;
+	$a = ['x' => 3, 'y' => 2];
 	sink($a);
 }
```

Or remove the offset from the literal if it is always set later:

```diff-php
 function doFoo(): void
 {
-	$a = ['x' => 1, 'y' => 2];
+	$a = ['y' => 2];
 	$a['x'] = 3;
 	sink($a);
 }
```

If the later write targets the wrong offset, fix the offset name:

```diff-php
 	$a = ['x' => 1, 'y' => 2];
-	$a['x'] = 3;
+	$a['z'] = 3;
 	sink($a);
```
