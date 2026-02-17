---
title: "property.readOnlyNotSupported"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public readonly int $value; // ERROR: Readonly properties are supported only on PHP 8.1 and later.

	public function __construct(int $value)
	{
		$this->value = $value;
	}
}
```

## Why is it reported?

The `readonly` modifier for class properties was introduced in PHP 8.1. Using `readonly` on a project configured with an earlier PHP version (e.g. PHP 8.0 or PHP 7.4) will result in this error because the code cannot run on the target PHP version.

## How to fix it

If upgrading PHP is possible, update the [phpVersion](https://phpstan.org/config-reference#phpversion) setting in the PHPStan configuration to match the actual PHP version being used:

```neon
parameters:
    phpVersion: 80100
```

If the project must support older PHP versions, remove the `readonly` modifier and enforce immutability through other means:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	public readonly int $value;
+	private int $value;

 	public function __construct(int $value)
 	{
 		$this->value = $value;
 	}
+
+	public function getValue(): int
+	{
+		return $this->value;
+	}
 }
```
