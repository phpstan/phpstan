---
title: "property.parentPropertyFinalByPhpDoc"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	/** @final */
	public int $value = 0;
}

class Bar extends Foo
{
	public int $value = 1; // ERROR: Property Bar::$value overrides @final property Foo::$value.
}
```

## Why is it reported?

The parent class has marked the property as `@final` in its PHPDoc, indicating that it should not be overridden in subclasses. The child class is overriding this property, which violates the parent's intent.

The `@final` PHPDoc annotation is a softer version of the PHP `final` keyword -- it signals the author's intent that the property should not be overridden, even though PHP does not enforce this at runtime. PHPStan enforces this restriction at the static analysis level.

## How to fix it

Remove the property override from the child class:

```diff-php
 <?php declare(strict_types = 1);

 class Bar extends Foo
 {
-	public int $value = 1;
 }
```

If the child class needs a different default value, set it in the constructor instead:

```diff-php
 <?php declare(strict_types = 1);

 class Bar extends Foo
 {
-	public int $value = 1;
+	public function __construct()
+	{
+		$this->value = 1;
+	}
 }
```
