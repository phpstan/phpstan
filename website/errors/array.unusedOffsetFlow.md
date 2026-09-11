---
title: "array.unusedOffsetFlow"
shortDescription: "A value stored under an array offset is read, but only to compute values that are themselves never used."
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
	while (rand(0, 1)) {
		$a['x'] = $a['x'] + 1;
	}
	sink($a['y']);
}
```

## Why is it reported?

The value stored under offset `'x'` of `$a` *is* read — but only by `$a['x'] = $a['x'] + 1`, whose result no code ever observes. Only offset `'y'` reaches a sink; the value under `'x'` feeds a closed computation that produces nothing.

This is different from [`array.unusedOffset`](/error-identifiers/array.unusedOffset), where the offset's value is never read at all. Here the value flows through further computation, but that computation is itself dead, so the whole chain has no effect. This often points to a leftover accumulator or a logic error where the offset was meant to be used.

This rule is part of PHPStan's dead code analysis. It is reported at [rule level](/user-guide/rule-levels) 4 and above, and is currently part of [Bleeding Edge](/blog/what-is-bleeding-edge).

## How to fix it

Remove the dead offset and its computation if the result is not needed:

```diff-php
 function doFoo(): void
 {
-	$a = ['x' => 1, 'y' => 2];
-	while (rand(0, 1)) {
-		$a['x'] = $a['x'] + 1;
-	}
+	$a = ['y' => 2];
 	sink($a['y']);
 }
```

Or read the offset if it was intended to be used:

```diff-php
 function doFoo(): void
 {
 	$a = ['x' => 1, 'y' => 2];
 	while (rand(0, 1)) {
 		$a['x'] = $a['x'] + 1;
 	}
+	sink($a['x']);
 	sink($a['y']);
 }
```
