---
title: "foreach.keyOverwritten"
shortDescription: "The key variable of a foreach loop is overwritten before it is ever read."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @param array<string, int> $data
 */
function doFoo(array $data): void
{
	foreach ($data as $key => $value) {
		$key = 'prefix';
		echo $key, $value;
	}
}
```

## Why is it reported?

The `foreach` loop binds each element's key to `$key`, but the loop body assigns a new value to `$key` before reading it. The key bound by the loop is never observed. This usually points to a logic error — the key was meant to be used — or to a variable name accidentally reused for a different value.

This is different from [`foreach.unusedKey`](/error-identifiers/foreach.unusedKey), where the key variable is simply never read. Here an assignment in the loop body overwrites it first.

This rule is part of PHPStan's dead code analysis. It is reported at [rule level](/user-guide/rule-levels) 4 and above, and is currently part of [Bleeding Edge](/blog/what-is-bleeding-edge).

## How to fix it

Use the key in the computation instead of discarding it:

```diff-php
 	foreach ($data as $key => $value) {
-		$key = 'prefix';
+		$key = 'prefix' . $key;
 		echo $key, $value;
 	}
```

If the key is not needed, drop it from the `foreach` and use a separate variable:

```diff-php
-	foreach ($data as $key => $value) {
-		$key = 'prefix';
-		echo $key, $value;
+	foreach ($data as $value) {
+		$prefix = 'prefix';
+		echo $prefix, $value;
 	}
```
