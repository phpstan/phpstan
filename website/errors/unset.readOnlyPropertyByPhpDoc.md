---
title: "unset.readOnlyPropertyByPhpDoc"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	/** @readonly */
	public int $value;

	public function __construct(int $value)
	{
		$this->value = $value;
	}

	public function reset(): void
	{
		unset($this->value);
	}
}
```

## Why is it reported?

The code attempts to `unset()` a property that is marked as `@readonly` in PHPDoc. Readonly properties should not be unset because they are intended to be immutable after initialization.

## How to fix it

Do not unset the readonly property. If you need to reset it, consider restructuring your code:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	/** @readonly */
 	public int $value;

 	public function __construct(int $value)
 	{
 		$this->value = $value;
 	}

-	public function reset(): void
-	{
-		unset($this->value);
-	}
 }
```
