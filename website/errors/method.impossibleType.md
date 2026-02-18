---
title: "method.impossibleType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public function isValid(): bool
	{
		return true;
	}
}

function doFoo(Foo $foo): void
{
	if ($foo->isValid()) {
		if ($foo->isValid()) {
			// ...
		}
	}
}
```

## Why is it reported?

A method call that acts as a type check will always evaluate to `false` based on the types PHPStan knows at that point. This means the condition can never be satisfied, so the code inside the branch is dead code.

Common examples include calling `is_*()` style methods on values whose type has already been narrowed, or calling type-checking methods on values that can never match the checked type.

## How to fix it

Remove the redundant check if the type has already been narrowed:

```diff-php
 function doFoo(Foo $foo): void
 {
 	if ($foo->isValid()) {
-		if ($foo->isValid()) {
-			// ...
-		}
+		// ...
 	}
 }
```

If the method should be able to return different values, fix the return type or the method's logic so that the check can actually evaluate to `false`.
