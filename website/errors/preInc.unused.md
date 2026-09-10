---
title: "preInc.unused"
shortDescription: "The result of a pre-increment (++$i) is never read."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
	$i = 0;
	echo $i;
	++$i;
}
```

## Why is it reported?

The pre-increment `++$i` raises `$i` by one, but that new value is never read afterwards. Incrementing a variable whose result nobody observes has no effect on the program. This usually indicates leftover code or a logic error where the incremented value was meant to be used.

This rule is part of PHPStan's dead code analysis. It is reported at [rule level](/user-guide/rule-levels) 4 and above, and is currently part of [Bleeding Edge](/blog/what-is-bleeding-edge).

## How to fix it

Remove the increment if the new value is not needed:

```diff-php
 function doFoo(): void
 {
 	$i = 0;
 	echo $i;
-	++$i;
 }
```

Or use the incremented value if it was intended to be read:

```diff-php
 function doFoo(): void
 {
 	$i = 0;
 	echo $i;
 	++$i;
+	echo $i;
 }
```
