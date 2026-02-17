---
title: "typeAlias.invalidType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @phpstan-type MyType array{name: string, value: }
 */
class Foo
{
}
```

## Why is it reported?

A type alias defined via `@phpstan-type` contains a type definition that cannot be parsed. The type syntax in the alias is malformed or uses unsupported constructs, resulting in an error type that PHPStan cannot resolve.

Common causes include:
- Syntax errors in the type definition (missing parts, extra commas, unclosed brackets)
- Using type syntax that PHPStan does not recognize

## How to fix it

Correct the type definition syntax in the `@phpstan-type` tag:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @phpstan-type MyType array{name: string, value: }
+ * @phpstan-type MyType array{name: string, value: mixed}
  */
 class Foo
 {
 }
```
