---
title: "typeAlias.invalidName"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @phpstan-type int \stdClass
 */
class Foo
{
}
```

## Why is it reported?

The name used for a PHPDoc type alias (`@phpstan-type` or `@phpstan-import-type ... as`) conflicts with a built-in PHP type name. Names like `int`, `string`, `float`, `bool`, `array`, `null`, `void`, `never`, `mixed`, `object`, `callable`, `iterable`, `self`, and `parent` cannot be used as type alias names because they already have special meaning in PHP's type system.

Using such a name would make it ambiguous whether the type refers to the built-in type or the alias.

## How to fix it

Choose a different name for the type alias that does not conflict with built-in PHP types:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @phpstan-type int \stdClass
+ * @phpstan-type MyObject \stdClass
  */
 class Foo
 {
 }
```

For imported type aliases, change the alias name:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @phpstan-import-type ExportedAlias from OtherClass as int
+ * @phpstan-import-type ExportedAlias from OtherClass as MyAlias
  */
 class Foo
 {
 }
```
