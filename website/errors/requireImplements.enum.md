---
title: "requireImplements.enum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

enum SomeEnum
{
    case Foo;
    case Bar;
}

/**
 * @phpstan-require-implements SomeEnum
 */
trait MyTrait {}
```

## Why is it reported?

The `@phpstan-require-implements` PHPDoc tag can only reference interfaces. Enums are not interfaces and cannot be "implemented" by a class. When a trait uses `@phpstan-require-implements`, it declares that any class using the trait must implement the specified interface. An enum cannot serve this role because PHP does not allow classes to implement enums.

## How to fix it

Replace the enum reference with an interface that the enum implements:

```diff-php
 <?php declare(strict_types = 1);

+interface SomeEnumInterface {}
+
+enum SomeEnum: string implements SomeEnumInterface
+{
+    case Foo = 'foo';
+    case Bar = 'bar';
+}
+
 /**
- * @phpstan-require-implements SomeEnum
+ * @phpstan-require-implements SomeEnumInterface
  */
 trait MyTrait {}
```

Or remove the `@phpstan-require-implements` tag if the constraint is not needed:

```diff-php
 <?php declare(strict_types = 1);

-/**
- * @phpstan-require-implements SomeEnum
- */
 trait MyTrait {}
```
