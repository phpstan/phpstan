---
title: "assign.readOnlyProperty"
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
