---
title: "nullCoalesce.offset"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
	$array = ['key' => 1];

	echo $array['nonexistent'] ?? 'default';
}
```

## Why is it reported?

The null coalesce operator (`??`) is used on an array offset that does not exist on the given type. The offset will never be found in the array, so the expression will always evaluate to the fallback value on the right side of `??`.

This typically indicates one of two problems:
- The offset key is misspelled
- The variable does not contain the expected array shape

## How to fix it

Fix the offset key to match what exists in the array:

```diff-php
 $array = ['key' => 1];

-echo $array['nonexistent'] ?? 'default';
+echo $array['key'] ?? 'default';
```

If the offset genuinely might not exist, adjust the array type to reflect that:

```diff-php
-/** @var array{key: int} $array */
+/** @var array{key?: int} $array */
```
