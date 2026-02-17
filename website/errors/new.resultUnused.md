---
title: "new.resultUnused"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Validator
{
	/** @phpstan-pure */
	public function __construct()
	{
	}
}

function doFoo(): void
{
	new Validator(); // ERROR: Call to new Validator() on a separate line has no effect.
}
```

## Why is it reported?

A `new ClassName()` expression appears as a standalone statement but the constructor has no side effects. The created object is not assigned to a variable, returned, or used in any way. Since the constructor is pure (or has no impure points), instantiating the object without using it has no observable effect and is likely a mistake.

## How to fix it

Use the result of the instantiation:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): void
 {
-	new Validator();
+	$validator = new Validator();
 }
```

If the constructor is intended to have side effects, make sure the constructor actually performs a side effect so PHPStan recognizes it as impure:

```diff-php
 <?php declare(strict_types = 1);

 class Validator
 {
-	/** @phpstan-pure */
 	public function __construct()
 	{
+		// perform some side effect
 	}
 }
```
