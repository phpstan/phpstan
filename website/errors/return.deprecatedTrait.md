---
title: "return.deprecatedTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewHelper instead */
trait OldHelper
{
}

class Foo
{
    public function create(): OldHelper
    {
        // ...
    }
}
```

## Why is it reported?

This error is reported by `phpstan/phpstan-deprecation-rules`.

The return type of a function or method references a trait marked with a `@deprecated` PHPDoc tag. Deprecated traits are scheduled for removal, and return types referencing them should be updated to use the recommended replacement.

## How to fix it

Replace the deprecated trait with its recommended replacement in the return type:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-    public function create(): OldHelper
+    public function create(): NewHelper
     {
         // ...
     }
 }
```
