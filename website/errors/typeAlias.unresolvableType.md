---
title: "typeAlias.unresolvableType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @phpstan-type MyType array{name: string, callback: callable(Foo): void}
 */
class Foo
{
}
```

## Why is it reported?

A type alias defined via `@phpstan-type` contains a type that PHPStan cannot fully resolve. This can happen when the type definition references itself through the class it is defined on, or when it contains complex type constructs that create resolution issues.

## How to fix it

Simplify the type definition to avoid unresolvable constructs:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @phpstan-type MyType array{name: string, callback: callable(Foo): void}
+ * @phpstan-type MyType array{name: string, callback: callable(mixed): void}
  */
 class Foo
 {
 }
```

Or use a separate class or interface as the type reference instead of a self-referencing construct.
