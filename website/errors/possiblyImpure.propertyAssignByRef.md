---
title: "possiblyImpure.propertyAssignByRef"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Counter
{
	public int $value = 0;

	/**
	 * @phpstan-pure
	 */
	public function getRef(): int
	{
		$ref = &$this->value;

		return 1;
	}
}
```

## Why is it reported?

The function or method is marked as `@phpstan-pure` but creates a reference to a property via `&$this->property` or `&$object->property`. Assigning by reference to a property is a potential side effect because modifying the reference variable later would also modify the property. Pure functions must not have side effects.

## How to fix it

Remove the by-reference assignment, or remove the `@phpstan-pure` annotation:

```diff-php
 /**
- * @phpstan-pure
  */
 public function getRef(): int
 {
-	$ref = &$this->value;
+	$ref = $this->value;

 	return 1;
 }
```
