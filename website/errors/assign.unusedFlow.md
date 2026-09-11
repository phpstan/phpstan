---
title: "assign.unusedFlow"
shortDescription: "A value assigned to a variable is read, but only to compute other values that are themselves never used."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
	$sum = 0;
	while (rand(0, 1)) {
		$sum = $sum + 1;
	}
}
```

## Why is it reported?

The value assigned to `$sum` *is* read — but only by expressions that feed back into `$sum` itself, whose final value no code ever observes. The assignment feeds a closed computation that produces nothing: it is a dead store chain.

This is different from [`assign.unused`](/error-identifiers/assign.unused), where the assigned value is never read at all. Here the value flows through further computation, but that computation is itself dead, so the whole chain has no effect. This usually points to a leftover accumulator or a logic error where the result was meant to be used.

This rule is part of PHPStan's dead code analysis. It is reported at [rule level](/user-guide/rule-levels) 4 and above, and is currently part of [Bleeding Edge](/blog/what-is-bleeding-edge).

## How to fix it

Remove the dead computation if its result is not needed:

```diff-php
 function doFoo(): void
 {
-	$sum = 0;
-	while (rand(0, 1)) {
-		$sum = $sum + 1;
-	}
 }
```

Or use the final value if it was intended to be read:

```diff-php
 function doFoo(): void
 {
 	$sum = 0;
 	while (rand(0, 1)) {
 		$sum = $sum + 1;
 	}
+	echo $sum;
 }
```
