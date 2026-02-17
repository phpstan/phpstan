---
title: "break.outOfLoop"
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
			break;
		}
	}
}
```

## Why is it reported?

The `break` keyword can only be used inside a loop (`for`, `foreach`, `while`, `do-while`) or a `switch` statement. Using `break` outside of these structures is a fatal error in PHP because there is no enclosing loop or switch to break out of.

In the example above, `break` is used inside an `if` statement that is not enclosed in any loop or switch, so PHP cannot determine what to break out of.

## How to fix it

Use `return` instead if the intent is to exit the function:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	public function doFoo(): void
 	{
 		if (rand(0, 1)) {
-			break;
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
 				break;
 			}
+		}
 	}
 }
```
