---
title: "continue.outOfLoop"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public function doFoo(): void
	{
		if (rand(0, 1)) {
			continue;
		}
	}
}
```

## Why is it reported?

The `continue` keyword can only be used inside a loop (`for`, `foreach`, `while`, `do-while`) or a `switch` statement. Using `continue` outside of these structures is a fatal error in PHP because there is no enclosing loop to continue to the next iteration of.

This also applies when `continue` appears inside a closure that is nested within a loop -- the closure creates a new scope, so `continue` inside it does not refer to the outer loop.

## How to fix it

Use `return` instead if the intent is to exit the function:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	public function doFoo(): void
 	{
 		if (rand(0, 1)) {
-			continue;
+			return;
 		}
 	}
 }
```

Or move the code inside a loop if a loop was intended:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	public function doFoo(): void
 	{
+		while (true) {
 			if (rand(0, 1)) {
 				continue;
 			}
+		}
 	}
 }
```
