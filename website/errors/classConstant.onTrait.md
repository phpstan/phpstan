---
title: "classConstant.onTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

trait MyTrait
{
	public const FOO = 'bar';
}

echo MyTrait::FOO;
```

## Why is it reported?

Accessing a constant directly on a trait name is not allowed. While traits can define constants (since PHP 8.2), those constants can only be accessed through a class that uses the trait, not through the trait itself. PHP does not support accessing trait constants via `TraitName::CONSTANT`.

In the example above, `MyTrait::FOO` attempts to access the constant `FOO` directly on the trait `MyTrait`.

## How to fix it

Access the constant through a class that uses the trait:

```diff-php
 <?php declare(strict_types = 1);

 trait MyTrait
 {
 	public const FOO = 'bar';
 }

 class MyClass
 {
 	use MyTrait;
 }

-echo MyTrait::FOO;
+echo MyClass::FOO;
```
