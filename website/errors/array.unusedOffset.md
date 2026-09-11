---
title: "array.unusedOffset"
shortDescription: "A value stored under an array offset is never read from that array."
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
	sink($a['y']);
}
```

## Why is it reported?

The array assigned to `$a` stores a value under offset `'x'`, but only offset `'y'` is ever read back from `$a`. The value under `'x'` is a dead store: it is computed and kept, but no code observes it.

Unlike [`variable.unused`](/error-identifiers/variable.unused), the array `$a` itself *is* used; only this particular offset is redundant. A dead offset often points to a leftover key or a logic error, such as reading the wrong offset name.

This rule is part of PHPStan's dead code analysis. It is reported at [rule level](/user-guide/rule-levels) 4 and above, and is currently part of [Bleeding Edge](/blog/what-is-bleeding-edge).

## How to fix it

Remove the offset whose value is never read:

```diff-php
 function doFoo(): void
 {
-	$a = ['x' => 1, 'y' => 2];
+	$a = ['y' => 2];
 	sink($a['y']);
 }
```

Or read the offset if it was intended to be used:

```diff-php
 function doFoo(): void
 {
 	$a = ['x' => 1, 'y' => 2];
+	sink($a['x']);
 	sink($a['y']);
 }
```
