---
title: "assign.readOnlyProperty"
shortDescription: "Readonly property is assigned more than once."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	private readonly int $value;

	public function __construct()
	{
		$this->value = 1;
		$this->value = 2;
	}
}
```

## Why is it reported?

A `readonly` property in PHP can only be assigned once. After its initial assignment, any further attempt to assign a value to it will cause a runtime error. PHPStan detects when a readonly property is assigned more than once in the constructor or across initialization paths.

In the example above, `$this->value` is assigned `1` and then immediately overwritten with `2`. Since the property is declared as `readonly`, the second assignment is invalid.

The same restriction applies inside `__clone()`. As of PHP 8.3, readonly properties may be reinitialized while cloning, but still only once — assigning the property a second time within `__clone()` is reported as well:

```php
<?php declare(strict_types = 1);

class Foo
{
	private readonly int $value;

	public function __construct()
	{
		$this->value = 1;
	}

	public function __clone()
	{
		$this->value = 2;
		$this->value = 3;
	}
}
```

## How to fix it

Remove the duplicate assignment and keep only the intended one:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	private readonly int $value;

 	public function __construct()
 	{
-		$this->value = 1;
 		$this->value = 2;
 	}
 }
```

Or use conditional logic to assign the property only once:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	private readonly int $value;

-	public function __construct()
+	public function __construct(bool $flag)
 	{
-		$this->value = 1;
-		$this->value = 2;
+		$this->value = $flag ? 1 : 2;
 	}
 }
```

The same approach applies inside `__clone()` — assign the property only once:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	private readonly int $value;

 	public function __construct()
 	{
 		$this->value = 1;
 	}

 	public function __clone()
 	{
-		$this->value = 2;
 		$this->value = 3;
 	}
 }
```
