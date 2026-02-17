---
title: "class.extendsInterface"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

interface BazInterface
{
}

class Foo extends BazInterface
{
}
```

## Why is it reported?

A class is attempting to extend an interface using the `extends` keyword. In PHP, classes cannot extend interfaces. Interfaces must be implemented using the `implements` keyword instead. The `extends` keyword is only valid for extending another class.

This is a compile-level error in PHP and will cause a fatal error at runtime.

## How to fix it

Use `implements` instead of `extends` when a class needs to use an interface:

```diff-php
 <?php declare(strict_types = 1);

 interface BazInterface
 {
 }

-class Foo extends BazInterface
+class Foo implements BazInterface
 {
 }
```
