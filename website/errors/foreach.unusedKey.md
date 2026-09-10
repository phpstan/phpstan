---
title: "foreach.unusedKey"
shortDescription: "The key variable of a foreach loop is never read in the loop body."
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
	}
}
```

## Why is it reported?

The `foreach` loop binds each element's key to `$key`, but the loop body never reads it. Capturing a key that nobody uses is unnecessary. This often means the body should use `$key` but doesn't, or the key does not need to be captured at all.

This rule is part of PHPStan's dead code analysis. It is reported at [rule level](/user-guide/rule-levels) 4 and above, and is currently part of [Bleeding Edge](/blog/what-is-bleeding-edge).

## How to fix it

Drop the key from the `foreach` if only the value is needed:

```diff-php
-	foreach ($data as $key => $value) {
+	foreach ($data as $value) {
 		echo $value;
 	}
```

Or use the key in the loop body if it was intended to be read:

```diff-php
 	foreach ($data as $key => $value) {
+		echo $key;
 		echo $value;
 	}
```

If you deliberately want to keep the key variable, prefix its name with an underscore. PHPStan ignores variables whose name starts with `_`:

```diff-php
-	foreach ($data as $key => $value) {
+	foreach ($data as $_key => $value) {
 		echo $value;
 	}
```
