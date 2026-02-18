---
title: "match.alwaysFalse"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @param 1|2|3 $i
 */
function doFoo(int $i): void
{
	match ($i) {
		'foo' => 'matched foo', // error: Match arm comparison between 1|2|3 and 'foo' is always false.
		default => 'default',
	};
}
```

## Why is it reported?

The `match` expression uses strict comparison (`===`) to evaluate each arm. When the subject type and the arm condition type have no overlap, the comparison will always evaluate to `false`, meaning the arm can never be matched. In the example above, the parameter `$i` is typed as `1|2|3` (integers), so comparing it against the string `'foo'` will never succeed.

## How to fix it

Remove the unreachable match arm or fix the arm condition to use a value that can actually match the subject.

```diff-php
 /**
  * @param 1|2|3 $i
  */
 function doFoo(int $i): void
 {
 	match ($i) {
-		'foo' => 'matched foo',
+		1 => 'matched one',
 		default => 'default',
 	};
 }
```
