---
title: "conditionalType.subjectNotFound"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
    /**
     * @return ($value is string ? int : bool)
     */
    public function doFoo(mixed $value): int|bool
    {
        // ...
    }
}
```

## Why is it reported?

A conditional return type uses a subject type that does not reference any `@template` tag. In a class method without `@template` tags, the subject of a conditional type must refer to a parameter using the `$param is Type` syntax rather than a bare type name.

## How to fix it

If the condition depends on a parameter, use the `$param is Type` syntax:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
     /**
-     * @return ($value is string ? int : bool)
+     * @param string|int $value
+     * @return ($value is string ? int : bool)
      */
-    public function doFoo(mixed $value): int|bool
+    public function doFoo(string|int $value): int|bool
     {
         // ...
     }
 }
```

If you intend to use a template type as the subject, declare it with `@template`:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
     /**
+     * @template T
-     * @return (int is string ? int : bool)
+     * @return (T is string ? int : bool)
      */
-    public function doFoo(mixed $value): int|bool
+    public function doFoo(mixed $value): int|bool
     {
         // ...
     }
 }
```
