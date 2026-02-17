---
title: "return.noParent"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

/**
 * @return parent
 */
function returnParent()
{
    // ...
}
```

## Why is it reported?

The `parent` keyword in PHP refers to the parent class of the current class. It can only be used inside a class that extends another class. When `parent` is used as a return type in a function (not a method), or in a class that does not extend any other class, it has no meaning because there is no parent class to refer to.

## How to fix it

Replace `parent` with the actual class name that was intended:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-/**
- * @return parent
- */
-function returnParent()
+function returnParent(): SomeClass
 {
     // ...
 }
```

If the code is in a class that should have a parent, add the `extends` clause:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-class MyClass
+class MyClass extends BaseClass
 {
     public function create(): parent
     {
         return new BaseClass();
     }
 }
```
