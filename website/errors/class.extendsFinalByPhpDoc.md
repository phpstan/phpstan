---
title: "class.extendsFinalByPhpDoc"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @final
 */
class ParentClass
{
}

class ChildClass extends ParentClass
{
}
```

## Why is it reported?

The class being extended is marked as `@final` in its PHPDoc. While PHP does not enforce this at runtime (unlike the native `final` keyword), the `@final` annotation signals that the class author considers it part of the API contract that the class should not be extended. Extending it may lead to breaking changes in the future when the class is modified.

Unlike the native `final` keyword which causes a PHP fatal error, `@final` is a soft constraint enforced by static analysis tools. This is why the error is ignorable.

## How to fix it

Use composition instead of inheritance:

```diff-php
 <?php declare(strict_types = 1);

 /**
  * @final
  */
 class ParentClass
 {
+	public function doSomething(): void
+	{
+	}
 }

-class ChildClass extends ParentClass
+class ChildClass
 {
+	public function __construct(
+		private ParentClass $parent,
+	) {
+	}
+
+	public function doSomething(): void
+	{
+		$this->parent->doSomething();
+	}
 }
```

If extending the class is intentional and the `@final` annotation should not apply, the error can be suppressed in the PHPStan configuration.
