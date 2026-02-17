---
title: "staticMethod.resultUnused"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
    public function doFoo(): void
    {
        \DateTimeImmutable::createFromFormat('Y-m-d', '2024-01-15');
    }
}
```

## Why is it reported?

The static method call on a separate line has no side effects, and its return value is not used. This means the call is pointless -- it computes a result that is immediately thrown away, without changing any state.

PHPStan detects this by checking whether the method has known side effects. Methods on immutable objects like `DateTimeImmutable` are pure and produce no side effects, so calling them without using the result serves no purpose.

## How to fix it

Assign the return value to a variable or use it in an expression:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
     public function doFoo(): void
     {
-        \DateTimeImmutable::createFromFormat('Y-m-d', '2024-01-15');
+        $date = \DateTimeImmutable::createFromFormat('Y-m-d', '2024-01-15');
     }
 }
```

If the method call is truly unnecessary, remove it:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
     public function doFoo(): void
     {
-        \DateTimeImmutable::createFromFormat('Y-m-d', '2024-01-15');
     }
 }
```
