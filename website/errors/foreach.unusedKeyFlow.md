---
title: "foreach.unusedKeyFlow"
shortDescription: "The key variable of a foreach loop is read, but only to compute values that are themselves never used."
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
		echo $value;
		while (rand(0, 1)) {
			$key = $key . '!';
		}
	}
}
```

## Why is it reported?

The `foreach` loop binds each element's key to `$key`, and that key *is* read — but only by `$key = $key . '!'`, whose result no code ever observes. The key feeds a closed computation that produces nothing.

This is different from [`foreach.unusedKey`](/error-identifiers/foreach.unusedKey), where the key variable is never read at all. Here the key flows through further computation, but that computation is itself dead, so the whole chain has no effect. This often means the loop body should use `$key` for something observable but doesn't, or the key does not need to be captured at all.

This rule is part of PHPStan's dead code analysis. It is reported at [rule level](/user-guide/rule-levels) 4 and above, and is currently part of [Bleeding Edge](/blog/what-is-bleeding-edge).

## How to fix it

Use the key for something observable if that was the intent:

```diff-php
 	foreach ($data as $key => $value) {
 		echo $value;
 		while (rand(0, 1)) {
 			$key = $key . '!';
+			echo $key;
 		}
 	}
```

Or drop the key from the `foreach` if only the value is needed:

```diff-php
-	foreach ($data as $key => $value) {
+	foreach ($data as $value) {
 		echo $value;
-		while (rand(0, 1)) {
-			$key = $key . '!';
-		}
 	}
```
