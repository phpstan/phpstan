---
title: "empty.initializedProperty"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class User
{
	private string $name;

	public function __construct(string $name)
	{
		$this->name = $name;
	}

	public function check(): void
	{
		if (empty($this->name)) {
			echo 'No name';
		}
	}
}
```

## Why is it reported?

The property used inside `empty()` has a native type that is not nullable and PHPStan can determine the property has been initialized. Since the property is always assigned a value and cannot be `null`, `empty()` cannot be checking for an uninitialized or null state -- it is only testing the falsiness of the value.

In the example above, `$this->name` is typed as `string` and is always initialized in the constructor. Using `empty()` on it is equivalent to checking whether it is a falsy string (empty string `''` or `'0'`), which should be expressed explicitly.

## How to fix it

Replace `empty()` with an explicit comparison that expresses the intended check:

```diff-php
 <?php declare(strict_types = 1);

 class User
 {
 	private string $name;

 	public function __construct(string $name)
 	{
 		$this->name = $name;
 	}

 	public function check(): void
 	{
-		if (empty($this->name)) {
+		if ($this->name === '') {
 			echo 'No name';
 		}
 	}
 }
```
