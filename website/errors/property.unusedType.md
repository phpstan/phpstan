---
title: "property.unusedType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Logger
{
	private string|int $level;

	public function __construct()
	{
		$this->level = 'info';
	}

	public function setLevel(string $level): void
	{
		$this->level = $level;
	}
}
```

## Why is it reported?

The property has a union type declaration, but one of the types in the union is never actually assigned to the property. PHPStan analyses all assignments to the property (including the default value) and determines that a part of the declared type is unused. In the example above, the property declares `string|int`, but only `string` values are ever assigned. The `int` part of the union type is unnecessary.

This helps catch overly broad type declarations that make the code harder to reason about and can hide type-related bugs.

## How to fix it

Remove the unused type from the property's type declaration:

```diff-php
 <?php declare(strict_types = 1);

 class Logger
 {
-	private string|int $level;
+	private string $level;

 	public function __construct()
 	{
 		$this->level = 'info';
 	}

 	public function setLevel(string $level): void
 	{
 		$this->level = $level;
 	}
 }
```

If the broader type is intentional because future code will assign values of the additional type, add the missing assignment path to make the type accurate.
