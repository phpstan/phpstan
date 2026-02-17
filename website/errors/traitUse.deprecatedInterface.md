---
title: "traitUse.deprecatedInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

/** @deprecated Use NewInterface instead */
interface OldInterface
{
    public function doSomething(): void;
}

class Foo
{
    use OldInterface;
}
```

## Why is it reported?

This error is reported by the `phpstan/phpstan-deprecation-rules` package.

The `use` statement inside a class body references an interface that is marked as `@deprecated`. While using an interface in a `use` statement is already incorrect (interfaces cannot be used as traits), the deprecation error is also reported because the referenced symbol is deprecated.

## How to fix it

Interfaces should be implemented using the `implements` keyword, not `use`. Replace the trait use with a proper `implements` declaration using the non-deprecated replacement:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-class Foo
+class Foo implements NewInterface
 {
-    use OldInterface;
+    public function doSomething(): void
+    {
+        // ...
+    }
 }
```

If the class is itself deprecated, the error will not be reported:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

+/** @deprecated */
 class Foo
 {
     use OldInterface;
 }
```
