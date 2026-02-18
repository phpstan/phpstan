---
title: "impure.propertyAssignByRef"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public int $value = 0;

	/** @phpstan-pure */
	public function doSomething(): int
	{
		$ref = &$this->value;

		return 1;
	}
}
```

## Why is it reported?

The method is declared as pure using `@phpstan-pure`, but it creates a reference to an object property (`$ref = &$this->value`). Assigning a variable by reference to a property is a possibly impure operation because modifying the reference variable later would mutate the object's state, which violates the purity contract.

Pure functions and methods must not have side effects -- they should only compute and return a value based on their inputs.

## How to fix it

If the method needs to reference object properties, remove the `@phpstan-pure` annotation:

```diff-php
-	/** @phpstan-pure */
 	public function doSomething(): int
 	{
 		$ref = &$this->value;

 		return 1;
 	}
```

If the method should remain pure, avoid creating references to properties:

```diff-php
 	/** @phpstan-pure */
 	public function doSomething(): int
 	{
-		$ref = &$this->value;
+		$ref = $this->value;

 		return 1;
 	}
```
