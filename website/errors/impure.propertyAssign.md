---
title: "impure.propertyAssign"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Counter
{
	public int $count = 0;

	/** @phpstan-pure */
	public function increment(): int
	{
		$this->count++;
		return $this->count;
	}
}
```

## Why is it reported?

A function or method marked as `@phpstan-pure` contains a property assignment. Pure functions must not have side effects, and modifying an object's property is a side effect because it changes the observable state.

## How to fix it

Remove the property assignment from the pure function:

```diff-php
 <?php declare(strict_types = 1);

 class Counter
 {
 	public int $count = 0;

-	/** @phpstan-pure */
-	public function increment(): int
+	public function increment(): int
 	{
 		$this->count++;
 		return $this->count;
 	}
 }
```

Or restructure the code so the pure function does not mutate state.
