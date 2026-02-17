---
title: "traitUse.deprecatedTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

/** @deprecated Use NewTrait instead */
trait OldTrait
{
    public function doSomething(): void
    {
    }
}

class Foo
{
    use OldTrait;
}
```

## Why is it reported?

This error is reported by the `phpstan/phpstan-deprecation-rules` package.

The class uses a trait that is marked as `@deprecated` via the `use` statement in the class body. Deprecated traits are scheduled for removal or replacement and should no longer be used in new code.

## How to fix it

Replace the deprecated trait with its recommended replacement:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

 class Foo
 {
-    use OldTrait;
+    use NewTrait;
 }
```

If the class is itself deprecated, the error will not be reported:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

+/** @deprecated */
 class Foo
 {
     use OldTrait;
 }
```
