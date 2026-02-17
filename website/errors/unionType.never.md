---
title: "unionType.never"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
    public function bar(string $b): never|string
    {
        if ($b === '') {
            throw new \RuntimeException('Error.');
        }

        return $b;
    }
}
```

## Why is it reported?

The `never` type cannot be part of a union type declaration. In PHP, `never` is a standalone type that indicates a function or method never returns (it always throws an exception or terminates the script). Combining `never` with other types in a union contradicts its meaning -- if the function can return a `string`, it does not "never return."

This is a PHP language restriction enforced at the parser level.

## How to fix it

Remove `never` from the union type. If the method can return a value, use only the types it can actually return:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-    public function bar(string $b): never|string
+    public function bar(string $b): string
     {
         if ($b === '') {
             throw new \RuntimeException('Error.');
         }

         return $b;
     }
 }
```

If the method truly never returns, use `never` as the sole return type:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-    public function bar(string $b): never|string
+    public function bar(string $b): never
     {
         throw new \RuntimeException($b);
     }
 }
```
