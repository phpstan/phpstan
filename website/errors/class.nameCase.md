---
title: "class.nameCase"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class MyClass
{

}

$obj = new myclass(); // reported: Class MyClass referenced with incorrect case: myclass.
```

## Why is it reported?

Although PHP class names are case-insensitive at runtime, referencing a class with incorrect letter casing is considered poor practice. It reduces code readability, can cause confusion, and may lead to issues with autoloaders on case-sensitive file systems (such as Linux). PHPStan reports this to encourage consistent and correct casing.

## How to fix it

Use the exact casing as declared in the class definition.

```diff-php
-$obj = new myclass();
+$obj = new MyClass();
```
