---
title: "constructor.missingParentCall"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class ParentClass
{
	public function __construct()
	{
		// initialization logic
	}
}

class ChildClass extends ParentClass
{
	public function __construct(private string $name)
	{
		// missing parent::__construct() call
	}
}
```

## Why is it reported?

This rule is part of [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules).

A child class defines its own constructor but does not call the parent class's constructor. When a parent class has a constructor with initialization logic, failing to call it from the child constructor can leave the object in an inconsistent or uninitialized state.

In the example above, `ChildClass::__construct()` does not call `parent::__construct()`, so the initialization logic in `ParentClass` is skipped.

## How to fix it

Call the parent constructor from the child constructor:

```diff-php
 <?php declare(strict_types = 1);

 class ChildClass extends ParentClass
 {
 	public function __construct(private string $name)
 	{
-		// missing parent::__construct() call
+		parent::__construct();
 	}
 }
```
