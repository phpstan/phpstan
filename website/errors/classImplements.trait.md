---
title: "classImplements.trait"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

trait MyTrait
{
}

class Foo implements MyTrait
{
}
```

## Why is it reported?

A class `implements` clause references a trait instead of an interface. In PHP, the `implements` keyword is only valid with interfaces. Traits must be used with the `use` keyword inside the class body.

## How to fix it

Use the `use` keyword to include the trait:

```diff-php
 <?php declare(strict_types = 1);

 trait MyTrait
 {
 }

-class Foo implements MyTrait
+class Foo
 {
+	use MyTrait;
 }
```
