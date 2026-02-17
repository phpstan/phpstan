---
title: "assign.propertyProtectedSet"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public protected(set) int $value = 0;
}

function doFoo(Foo $foo): void
{
	$foo->value = 5;
}
```

## Why is it reported?

The property uses asymmetric visibility with `protected(set)`, meaning it can be read publicly but can only be written to from within the declaring class or its subclasses. The code is attempting to assign a value to this property from outside the class hierarchy, which violates the write visibility restriction.

Asymmetric visibility is a PHP 8.4+ feature that allows separate read and write access levels for properties.

In the example above, `$foo->value` is publicly readable but can only be assigned from within `Foo` or its subclasses. The assignment in `doFoo()` is outside the class hierarchy and therefore not allowed.

## How to fix it

Use a public method on the class to modify the property:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	public protected(set) int $value = 0;

+	public function setValue(int $value): void
+	{
+		$this->value = $value;
+	}
 }

 function doFoo(Foo $foo): void
 {
-	$foo->value = 5;
+	$foo->setValue(5);
 }
```

Or change the property's write visibility if external writes are intended:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	public protected(set) int $value = 0;
+	public int $value = 0;
 }

 function doFoo(Foo $foo): void
 {
 	$foo->value = 5;
 }
```
