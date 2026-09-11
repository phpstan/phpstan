---
title: "foreach.unusedValueFlow"
shortDescription: "The value variable of a foreach loop is read, but only to compute values that are themselves never used."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @param list<int> $numbers
 */
function doFoo(array $numbers): void
{
	foreach ($numbers as $value) {
		while (rand(0, 1)) {
			$value = $value + 1;
		}
	}
}
```

## Why is it reported?

The `foreach` loop binds each element to `$value`, and that value *is* read — but only by `$value = $value + 1`, whose result no code ever observes. The value feeds a closed computation that produces nothing.

This is different from [`foreach.unusedValue`](/error-identifiers/foreach.unusedValue), where the value variable is never read at all. Here the value flows through further computation, but that computation is itself dead, so the whole chain has no effect. This often means the loop body should use `$value` for something observable but doesn't, or the loop only needs the keys.

This rule is part of PHPStan's dead code analysis. It is reported at [rule level](/user-guide/rule-levels) 4 and above, and is currently part of [Bleeding Edge](/blog/what-is-bleeding-edge).

## How to fix it

Use the value for something observable if that was the intent:

```diff-php
 	foreach ($numbers as $value) {
 		while (rand(0, 1)) {
 			$value = $value + 1;
 		}
+		echo $value;
 	}
```

Or drop the loop entirely if it does nothing:

```diff-php
-	foreach ($numbers as $value) {
-		while (rand(0, 1)) {
-			$value = $value + 1;
-		}
-	}
```
