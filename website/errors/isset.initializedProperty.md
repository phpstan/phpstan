---
title: "isset.initializedProperty"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public int $value = 0;

	public function doFoo(): void
	{
		if (isset($this->value)) {
			echo $this->value;
		}
	}
}
```

## Why is it reported?

The `isset()` check (or `??` null-coalescing operator, or `empty()`) is used on a property that has a native type and is known to be initialized. A typed property that is initialized can never be `null` (unless `null` is part of its declared type) and can never be in an uninitialized state at the point of the check. The `isset()` call is therefore redundant -- it always evaluates to `true`.

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

If the property might legitimately be uninitialized in some code paths, declare it without a default value and consider making it nullable:

```diff-php
-public int $value = 0;
+public ?int $value = null;
```
