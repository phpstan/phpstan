---
title: "foreach.unusedValue"
shortDescription: "The value variable of a foreach loop is never read in the loop body."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @param list<int> $data
 */
function doFoo(array $data): int
{
	$count = 0;
	foreach ($data as $value) {
		$count++;
	}

	return $count;
}
```

## Why is it reported?

The `foreach` loop binds each element to `$value`, but the loop body never reads it. Assigning a value that nobody uses has no effect. This often means the body should use `$value` but doesn't.

When the loop also binds a key (`foreach ($data as $key => $value)`) and only the key is used, the unused value is not reported, because PHP has no syntax for binding just the key.

This rule is part of PHPStan's dead code analysis. It is reported at [rule level](/user-guide/rule-levels) 4 and above, and is currently part of [Bleeding Edge](/blog/what-is-bleeding-edge).

## How to fix it

Use the value in the loop body if it was intended to be read:

```diff-php
 	foreach ($data as $value) {
-		$count++;
+		$count += $value;
 	}
```

If the loop only counts or repeats something, a function like `count()` may express the intent more directly:

```diff-php
-	$count = 0;
-	foreach ($data as $value) {
-		$count++;
-	}
-
-	return $count;
+	return count($data);
```

If you deliberately want to keep the value variable, prefix its name with an underscore. PHPStan ignores variables whose name starts with `_`:

```diff-php
-	foreach ($data as $value) {
+	foreach ($data as $_value) {
 		$count++;
 	}
```
