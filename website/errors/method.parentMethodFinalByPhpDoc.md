---
title: "method.parentMethodFinalByPhpDoc"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class ParentClass
{
	/**
	 * @final
	 */
	public function doFoo(): void
	{
	}
}

class ChildClass extends ParentClass
{
	public function doFoo(): void
	{
	}
}
```

## Why is it reported?

A method overrides a parent method that is marked as `@final` in PHPDoc. The `@final` annotation indicates that the method author intended it to not be overridden, even though it is not enforced at the PHP language level with the `final` keyword.

## How to fix it

Do not override the `@final` method. Use composition or a different approach:

```diff-php
 <?php declare(strict_types = 1);

 class ChildClass extends ParentClass
 {
-	public function doFoo(): void
+	public function doBar(): void
 	{
+		$this->doFoo();
+		// additional logic
 	}
 }
```
