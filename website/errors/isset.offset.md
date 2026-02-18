---
title: "isset.offset"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
	$array = ['a' => 1, 'b' => 2];

	if (isset($array['a'])) {
		echo $array['a'];
	}
}
```

## Why is it reported?

The `isset()` check (or `??` null-coalescing operator, or `empty()`) is used on an array offset that PHPStan can determine either always exists and is not nullable, or never exists. When the offset is always present and its value is never `null`, the `isset()` call is redundant -- it always evaluates to `true`. When the offset can never exist on the given type, the check always evaluates to `false`.

## How to fix it

If the offset always exists, remove the unnecessary `isset()` check:

```diff-php
 $array = ['a' => 1, 'b' => 2];

-if (isset($array['a'])) {
-	echo $array['a'];
-}
+echo $array['a'];
```

If the offset never exists, fix the key or the source of the data:

```diff-php
 $array = ['a' => 1, 'b' => 2];

-if (isset($array['c'])) {
+if (isset($array['a'])) {
 	// ...
 }
```

If the array contents are dynamic and the check is meaningful, use a broader type annotation to tell PHPStan about the possible keys:

```diff-php
-/** @var array{a: int, b: int} $array */
+/** @var array<string, int> $array */
```
