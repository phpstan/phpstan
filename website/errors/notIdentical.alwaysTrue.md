---
title: "notIdentical.alwaysTrue"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $i, string $s): void
{
	if ($i !== $s) { // error: Strict comparison using !== between int and string will always evaluate to true.
		// ...
	}
}
```

## Why is it reported?

The strict not-identical operator (`!==`) compares both value and type. When the two operands can never be of the same type, the comparison will always evaluate to `true`, making the condition meaningless. This usually indicates a logic error -- the code is checking something that can never be false.

This error can be turned off for the last condition in an if/elseif chain via the [`reportAlwaysTrueInLastCondition`](/config-reference#reportalwaystrueInlastcondition) option.

## How to fix it

Compare values of compatible types, or remove the unnecessary condition.

```diff-php
-function doFoo(int $i, string $s): void
+function doFoo(int $i, int $j): void
 {
-	if ($i !== $s) {
+	if ($i !== $j) {
 		// ...
 	}
 }
```

If comparing different representations of the same logical value, convert one operand first.

```diff-php
 function doFoo(int $i, string $s): void
 {
-	if ($i !== $s) {
+	if ($i !== (int) $s) {
 		// ...
 	}
 }
```
