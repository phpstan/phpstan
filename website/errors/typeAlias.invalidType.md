---
title: "typeAlias.invalidType"
shortDescription: "Type alias contains a malformed or unparseable type definition."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @phpstan-type MyType int<0>
 */
class Foo
{
}
```

## Why is it reported?

A type alias defined via `@phpstan-type` contains a type definition that resolves to an invalid type. While the type syntax may be parseable, it produces an error type that PHPStan cannot work with.

Common causes include:
- Using invalid generic type arguments (e.g. `int<0>` instead of `int<0, max>`)
- Type definitions that resolve to impossible or contradictory types

## How to fix it

Correct the type definition in the `@phpstan-type` tag:

```diff-php
 /**
- * @phpstan-type MyType int<0>
+ * @phpstan-type MyType int<0, max>
  */
 class Foo
 {
 }
```
