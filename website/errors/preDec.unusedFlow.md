---
title: "preDec.unusedFlow"
shortDescription: "The result of a pre-decrement (--$i) is read, but only to compute values that are themselves never used."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
	$i = 10;
	while (rand(0, 1)) {
		--$i;
	}
}
```

## Why is it reported?

The pre-decrement `--$i` lowers `$i` by one, and that new value *is* read — but only by the next iteration's `--$i`, whose result no code ever observes. The decrement feeds a closed computation that produces nothing.

This is different from [`preDec.unused`](/error-identifiers/preDec.unused), where the decremented value is never read at all. Here the value flows through further computation, but that computation is itself dead, so the whole chain has no effect. This usually points to a leftover counter or a logic error where the result was meant to be used.

This rule is part of PHPStan's dead code analysis. It is reported at [rule level](/user-guide/rule-levels) 4 and above, and is currently part of [Bleeding Edge](/blog/what-is-bleeding-edge).

## How to fix it

Remove the dead computation if its result is not needed:

```diff-php
 function doFoo(): void
 {
-	$i = 10;
-	while (rand(0, 1)) {
-		--$i;
-	}
 }
```

Or use the final value if it was intended to be read:

```diff-php
 function doFoo(): void
 {
 	$i = 10;
 	while (rand(0, 1)) {
 		--$i;
 	}
+	echo $i;
 }
```
