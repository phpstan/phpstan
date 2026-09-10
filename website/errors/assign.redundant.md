---
title: "assign.redundant"
shortDescription: "A variable is assigned a value it already holds."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(bool $c): int
{
	$a = 1;
	if ($c) {
		$a = 1;
	}

	return $a;
}
```

## Why is it reported?

The assignment `$a = 1` inside the `if` block gives `$a` a value it already has, so it changes nothing. PHPStan tracks that `$a` is `1` at that point, making the second assignment redundant.

A redundant assignment usually signals a copy-paste mistake or a condition that was meant to assign a different value.

This rule is part of PHPStan's dead code analysis. It is reported at [rule level](/user-guide/rule-levels) 4 and above, and is currently part of [Bleeding Edge](/blog/what-is-bleeding-edge).

## How to fix it

Remove the redundant assignment:

```diff-php
 function doFoo(bool $c): int
 {
 	$a = 1;
-	if ($c) {
-		$a = 1;
-	}

 	return $a;
 }
```

Or, if the branch was meant to assign a different value, correct it:

```diff-php
 	$a = 1;
 	if ($c) {
-		$a = 1;
+		$a = 2;
 	}
```
