---
title: "class.extendsTrait"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

trait SomeTrait
{

}

class Foo extends SomeTrait // reported
{

}
```

## Why is it reported?

A class cannot extend a trait. Traits are designed for horizontal code reuse via the `use` keyword inside a class body, not for inheritance via `extends`. Attempting to extend a trait results in a fatal error at runtime.

## How to fix it

Use the trait inside the class body with the `use` keyword instead of extending it.

```diff-php
-class Foo extends SomeTrait
+class Foo
 {
+    use SomeTrait;
 }
```
