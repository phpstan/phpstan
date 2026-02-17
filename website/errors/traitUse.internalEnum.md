---
title: "traitUse.internalEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In package vendor/some-library:

namespace SomeLibrary;

/** @internal */
enum InternalStatus: string
{
	case Active = 'active';
	case Inactive = 'inactive';
}
```

```php
<?php declare(strict_types = 1);

// In your code:

namespace App;

use SomeLibrary\InternalStatus;

class Foo
{
    use InternalStatus;
}
```

## Why is it reported?

The `use` statement inside a class body references an enum that is marked as `@internal`. Internal enums are not part of the public API of the package that defines them. They may change or be removed in any version without notice.

While using an enum in a `use` statement is already incorrect (only traits can be used this way), the internal access error is also reported because the referenced symbol is internal to another package.

## How to fix it

Only traits can be used in a class body `use` statement. If you need functionality from the library, look for a public trait that provides it:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use SomeLibrary\InternalStatus;
+use SomeLibrary\PublicTrait;

 class Foo
 {
-    use InternalStatus;
+    use PublicTrait;
 }
```

If the enum is internal to your own project, the error will not be reported when referencing it from within the same root namespace.
