---
title: "isset.initializedProperty"
shortDescription: "Property in isset() is always initialized and non-null."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	private int $value;

	public function __construct(int $value)
	{
		$this->value = $value;
	}

	public function doFoo(): void
	{
		if (isset($this->value)) {
			echo $this->value;
		}
	}
}
```

## Why is it reported?

The `isset()` check is used on a property that has a native type and is known to be initialized. PHPStan determined that `$this->value` is always assigned in the constructor, so the property can never be in an uninitialized state at the point of the check. Since the type `int` is also not nullable, `isset()` always evaluates to `true`, making the check redundant.

## How to fix it

Remove the unnecessary `isset()` check:

```diff-php
 public function doFoo(): void
 {
-	if (isset($this->value)) {
-		echo $this->value;
-	}
+	echo $this->value;
 }
```

If the property might legitimately be uninitialized in some code paths, consider making it nullable:

```diff-php
-private int $value;
+private ?int $value = null;
```
