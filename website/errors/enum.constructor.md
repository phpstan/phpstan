---
title: "enum.constructor"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

enum Suit
{
    case Hearts;
    case Diamonds;

    public function __construct()
    {
    }
}
```

## Why is it reported?

PHP enums cannot define a constructor. Enum cases are instantiated by PHP itself and are not meant to be created manually. Declaring a `__construct` method in an enum is a compile-time error.

## How to fix it

Remove the constructor from the enum. If you need initialization logic, use a regular method instead:

```diff-php
 <?php declare(strict_types = 1);

 enum Suit
 {
     case Hearts;
     case Diamonds;

-    public function __construct()
-    {
-    }
+    public function label(): string
+    {
+        return match($this) {
+            self::Hearts => 'Hearts',
+            self::Diamonds => 'Diamonds',
+        };
+    }
 }
```
