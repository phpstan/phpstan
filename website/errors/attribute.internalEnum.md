---
title: "attribute.internalEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In package vendor/some-library:

namespace SomeLibrary;

/** @internal */
enum Priority: int
{
	case Low = 1;
	case High = 2;
}
```

```php
<?php declare(strict_types = 1);

// In your code:

namespace App;

use SomeLibrary\Priority;

#[Priority]
class Foo
{
}
```

## Why is it reported?

An attribute references an enum that is marked as `@internal`. Internal types are not meant to be used outside of the package or namespace where they are defined. Depending on internal types in your attributes creates a fragile dependency on implementation details that can change without notice.

In the example above, the `#[Priority]` attribute references the internal `Priority` enum from `SomeLibrary`.

## How to fix it

Use a public (non-internal) type as the attribute instead. If the library provides a public attribute class or enum, use that. Otherwise, define your own attribute:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use SomeLibrary\Priority;
+#[\Attribute]
+class Priority
+{
+	public function __construct(public int $level = 1)
+	{
+	}
+}

-#[Priority]
+#[Priority(level: 2)]
 class Foo
 {
 }
```
