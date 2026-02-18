---
title: "attribute.internalClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In package vendor/some-library:

namespace SomeLibrary;

/** @internal */
class InternalAttribute
{
}
```

```php
<?php declare(strict_types = 1);

// In your code:

namespace App;

use SomeLibrary\InternalAttribute;

#[InternalAttribute]
class Foo
{
}
```

## Why is it reported?

An attribute references a class that is marked as `@internal`. Internal types are not meant to be used outside of the package or namespace where they are defined. Depending on internal classes in your attributes creates a fragile dependency on implementation details that can change without notice.

## How to fix it

Use a public (non-internal) attribute class instead:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use SomeLibrary\InternalAttribute;
+use SomeLibrary\PublicAttribute;

-#[InternalAttribute]
+#[PublicAttribute]
 class Foo
 {
 }
```
