---
title: "enum.implementsInternalTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In package vendor/some-package:
namespace SomePackage;

/** @internal */
trait InternalHelper {}
```

```php
<?php declare(strict_types = 1);

// In your code:
use SomePackage\InternalHelper;

enum MyEnum implements InternalHelper
{
	case Foo;
}
```

## Why is it reported?

The enum references an internal trait in its `implements` clause. The trait is marked with `@internal`, meaning it is not meant to be used outside the package that defines it. Internal types can change or be removed without notice in future versions.

Additionally, using a trait in an `implements` clause is invalid PHP. Traits should be used with the `use` keyword inside the enum body, not in the `implements` clause.

## How to fix it

If an interface is intended, use the correct non-internal interface:

```diff-php
-enum MyEnum implements InternalHelper
+enum MyEnum implements PublicInterface
 {
 	case Foo;
 }
```

If a trait is intended, move it to a `use` statement inside the enum body and ensure the trait is not internal:

```diff-php
-enum MyEnum implements InternalHelper
+enum MyEnum
 {
+	use SomePublicTrait;
+
 	case Foo;
 }
```
