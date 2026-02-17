---
title: "nullsafe.assign"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public function doFoo(?\stdClass $a): void
	{
		$a?->foo = 'bar'; // ERROR: Nullsafe operator cannot be on left side of assignment.
	}
}
```

## Why is it reported?

PHP does not allow the nullsafe operator (`?->`) on the left side of an assignment. The nullsafe operator is designed for reading values and short-circuiting to `null` when the object is `null`. Assigning a value through the nullsafe operator is a syntax error in PHP because the semantics of what should happen when the object is `null` during assignment are undefined.

This is a language-level restriction that cannot be worked around.

## How to fix it

Use a regular null check before the assignment:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	public function doFoo(?\stdClass $a): void
 	{
-		$a?->foo = 'bar';
+		if ($a !== null) {
+			$a->foo = 'bar';
+		}
 	}
 }
```
