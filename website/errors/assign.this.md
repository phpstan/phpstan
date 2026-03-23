---
title: "assign.this"
shortDescription: "Assigning a value to the `$this` variable."
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public function doFoo(): void
	{
		$this = new self();
	}
}
```

## Why is it reported?

PHP does not allow re-assigning `$this`. The `$this` variable is a special read-only reference to the current object instance and cannot be used as an assignment target. This applies to direct assignments, compound assignments, array destructuring, and `foreach` loop variables.

This error is reported regardless of context — inside instance methods, static methods, and even standalone functions where `$this` is not available at all.

This error is not ignorable.

## How to fix it

Use a different variable name:

```diff-php
 class Foo
 {
 	public function doFoo(): void
 	{
-		$this = new self();
+		$instance = new self();
 	}
 }
```

When `$this` appears as a `foreach` loop variable, rename it:

```diff-php
-foreach ($items as $this) {
+foreach ($items as $item) {
 	var_dump($item);
 }
```
