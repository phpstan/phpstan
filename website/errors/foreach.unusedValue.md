---
title: "foreach.unusedValue"
shortDescription: "The value variable of a foreach loop is never read in the loop body."
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
		echo $key;
	}
}
```

## Why is it reported?

The `foreach` loop binds each element's value to `$value`, but the loop body never reads it. Assigning a value that nobody uses has no effect. This often means the body should use `$value` but doesn't, or the loop only needs the keys.

This rule is part of PHPStan's dead code analysis. It is reported at [rule level](/user-guide/rule-levels) 4 and above, and is currently part of [Bleeding Edge](/blog/what-is-bleeding-edge).

## How to fix it

If only the keys are needed, iterate over `array_keys()`:

```diff-php
-	foreach ($data as $key => $value) {
+	foreach (array_keys($data) as $key) {
 		echo $key;
 	}
```

Or use the value in the loop body if it was intended to be read:

```diff-php
 	foreach ($data as $key => $value) {
 		echo $key;
+		echo $value;
 	}
```

If you deliberately want to keep the value variable, prefix its name with an underscore. PHPStan ignores variables whose name starts with `_`:

```diff-php
-	foreach ($data as $key => $value) {
+	foreach ($data as $key => $_value) {
 		echo $key;
 	}
```
