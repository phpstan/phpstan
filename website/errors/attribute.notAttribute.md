---
title: "attribute.notAttribute"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class MyClass
{
}

#[MyClass]
class Foo
{
}
```

## Why is it reported?

A class is used as a PHP attribute, but it is not declared as an attribute class. In PHP 8.0+, a class must be marked with the built-in `#[\Attribute]` attribute to be usable as an attribute. Without this declaration, PHP will throw an error at runtime.

In the example above, `MyClass` is used as `#[MyClass]` but is not annotated with `#[\Attribute]`.

## How to fix it

Add the `#[\Attribute]` declaration to the class:

```diff-php
 <?php declare(strict_types = 1);

+#[\Attribute]
 class MyClass
 {
 }

 #[MyClass]
 class Foo
 {
 }
```

If the class was not intended to be used as an attribute, remove the attribute usage:

```diff-php
 <?php declare(strict_types = 1);

 class MyClass
 {
 }

-#[MyClass]
 class Foo
 {
 }
```
