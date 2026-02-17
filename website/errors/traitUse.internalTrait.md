---
title: "traitUse.internalTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In package vendor/some-library:

namespace SomeLibrary;

/** @internal */
trait InternalTrait
{
    public function doSomething(): void
    {
        // ...
    }
}
```

```php
<?php declare(strict_types = 1);

// In your code:

namespace App;

use SomeLibrary\InternalTrait;

class Foo
{
    use InternalTrait;
}
```

## Why is it reported?

The `use` statement inside a class body references a trait that is marked as `@internal`. Internal traits are not part of the public API of the package that defines them. They may change or be removed in any version without notice.

Using an internal trait from another package creates a dependency on implementation details that are not guaranteed to be stable.

## How to fix it

Use a public (non-internal) trait from the package instead:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use SomeLibrary\InternalTrait;
+use SomeLibrary\PublicTrait;

 class Foo
 {
-    use InternalTrait;
+    use PublicTrait;
 }
```

If the trait is internal to your own project, the error will not be reported when referencing it from within the same root namespace.
