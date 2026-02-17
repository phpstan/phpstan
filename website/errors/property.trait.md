---
title: "property.trait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

trait MyTrait
{
}

class Foo
{
	public MyTrait $bar; // ERROR: Property Foo::$bar has invalid type MyTrait.
}
```

## Why is it reported?

A trait is being used as a type for a class property. In PHP, traits are not types -- they are a mechanism for horizontal code reuse. A trait cannot be used as a type declaration for properties, parameters, or return types. Unlike classes and interfaces, traits do not define a type that objects can be checked against.

## How to fix it

Use an interface or class as the property type instead of a trait:

```diff-php
 <?php declare(strict_types = 1);

-trait MyTrait
+interface MyInterface
 {
 }

 class Foo
 {
-	public MyTrait $bar;
+	public MyInterface $bar;
 }
```

If the trait defines shared behavior, extract an interface that classes using the trait should implement:

```diff-php
 <?php declare(strict_types = 1);

+interface HasName
+{
+	public function getName(): string;
+}
+
 trait NameTrait
 {
 	public function getName(): string
 	{
 		return $this->name;
 	}
 }

 class Foo
 {
-	public NameTrait $entity;
+	public HasName $entity;
 }
```
