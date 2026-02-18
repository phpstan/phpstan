---
title: "typeAlias.unresolvableType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @phpstan-type Callback callable(Foo): void
 */
class Foo
{
}
```

## Why is it reported?

A type alias defined via `@phpstan-type` contains a type that PHPStan cannot fully resolve. This can happen when the type definition references the class it is defined on in a way that creates a circular reference, or when it contains type constructs that lead to resolution issues.

In the example above, the `Callback` type alias references `Foo` in a callable parameter, but since the type alias is defined on `Foo` itself, this creates a circular reference that PHPStan cannot resolve.

## How to fix it

Avoid self-referencing constructs in the type definition:

```diff-php
 /**
- * @phpstan-type Callback callable(Foo): void
+ * @phpstan-type Callback callable(mixed): void
  */
 class Foo
 {
 }
```

Or define the type alias on a different class that does not create a circular reference.
