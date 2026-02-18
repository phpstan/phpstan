---
title: "assign.readOnlyPropertyByPhpDoc"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
    /** @readonly */
    private int $value;

    public function __construct()
    {
        $this->value = 1;
        $this->value = 2;
    }
}
```

## Why is it reported?

A property marked with the `@readonly` PHPDoc tag is being assigned more than once. The `@readonly` annotation indicates that the property should only be assigned in the constructor and must not be modified afterwards. Assigning it multiple times, even within the constructor, violates this contract because the intent is for the property to be set exactly once.

## How to fix it

Remove the duplicate assignment:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
     /** @readonly */
     private int $value;

     public function __construct()
     {
-        $this->value = 1;
         $this->value = 2;
     }
 }
```

Or, on PHP 8.1+, use the native `readonly` modifier instead which enforces single-assignment at the language level:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-    /** @readonly */
-    private int $value;
+    private readonly int $value;

     public function __construct()
     {
         $this->value = 1;
-        $this->value = 2;
     }
 }
```
