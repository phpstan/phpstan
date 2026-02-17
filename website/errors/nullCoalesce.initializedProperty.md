---
title: "nullCoalesce.initializedProperty"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class User
{
	private string $name;

	public function __construct()
	{
		$this->name = 'John';
	}

	public function doFoo(): void
	{
		echo $this->name ?? 'default'; // ERROR: Property User::$name on left side of ?? is not nullable and initialized.
	}
}
```

## Why is it reported?

The `??` (null coalescing) operator is designed to provide a fallback value when the left side is `null` or undefined. In this case, PHPStan has determined that the property being checked with `??` has a non-nullable native type and is guaranteed to be initialized at the point of access. Since the property can never be `null` and is always initialized, the right side of `??` will never be used, making the null coalescing operator redundant.

## How to fix it

Remove the null coalescing operator since the property is always initialized and non-nullable:

```diff-php
 <?php declare(strict_types = 1);

 class User
 {
 	private string $name;

 	public function __construct()
 	{
 		$this->name = 'John';
 	}

 	public function doFoo(): void
 	{
-		echo $this->name ?? 'default';
+		echo $this->name;
 	}
 }
```

Or if the property should legitimately be nullable, change its type:

```diff-php
 <?php declare(strict_types = 1);

 class User
 {
-	private string $name;
+	private ?string $name;

 	public function __construct()
 	{
 		$this->name = 'John';
 	}

 	public function doFoo(): void
 	{
 		echo $this->name ?? 'default';
 	}
 }
```
