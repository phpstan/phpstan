---
title: "return.deprecatedClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

/** @deprecated Use NewService instead */
class OldService
{
}

function createService(): OldService
{
    return new OldService();
}
```

## Why is it reported?

This error is reported by the `phpstan/phpstan-deprecation-rules` package.

The native return type declaration of a function or method references a class that is marked as `@deprecated`. Using a deprecated class in a return type creates a dependency on a symbol that is scheduled for removal or replacement. Callers of this function will also depend on the deprecated class.

## How to fix it

Replace the deprecated class in the return type with its recommended replacement:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-function createService(): OldService
+function createService(): NewService
 {
-    return new OldService();
+    return new NewService();
 }
```

If the function is itself deprecated, the error will not be reported. Mark the function as deprecated to suppress the error if the function is part of a deprecation migration:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

+/** @deprecated Use createNewService() instead */
 function createService(): OldService
 {
     return new OldService();
 }
```
