---
title: "foreach.valueOverwritten"
shortDescription: "The value variable of a foreach loop is overwritten before it is ever read."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @param list<int> $items
 */
function doFoo(array $items): void
{
	foreach ($items as $value) {
		$value = rand();
		echo $value;
	}
}
```

## Why is it reported?

The `foreach` loop binds each element to `$value`, but the loop body assigns a new value to `$value` before reading it. The element bound by the loop is never observed. This usually points to a logic error — the element was meant to be used — or to a variable name accidentally reused for a different value.

This is different from [`foreach.unusedValue`](/error-identifiers/foreach.unusedValue), where the value variable is simply never read. Here an assignment in the loop body overwrites it first.

This rule is part of PHPStan's dead code analysis. It is reported at [rule level](/user-guide/rule-levels) 4 and above, and is currently part of [Bleeding Edge](/blog/what-is-bleeding-edge).

## How to fix it

Use the element before overwriting the variable, or use it in the computation:

```diff-php
 	foreach ($items as $value) {
-		$value = rand();
+		$value = $value + rand();
 		echo $value;
 	}
```

Use a different variable for the new value so the element is not lost:

```diff-php
 	foreach ($items as $value) {
-		$value = rand();
-		echo $value;
+		$random = rand();
+		echo $value, $random;
 	}
```
