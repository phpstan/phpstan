---
title: "typeAlias.duplicate"
shortDescription: "Type alias name conflicts with an existing class or type in scope."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class SomeClass {}

/**
 * @phpstan-type SomeClass string
 */
class AnotherClass
{
}
```

## Why is it reported?

A type alias defined via `@phpstan-type` or imported via `@phpstan-import-type` conflicts with an existing class, interface, trait, or enum name in the current scope. In the example above, defining a type alias named `SomeClass` conflicts with the existing `SomeClass` class.

This ambiguity would make it unclear whether `SomeClass` refers to the actual class or the type alias.

## How to fix it

Choose a different name for the type alias that does not conflict with an existing type:

```diff-php
 /**
- * @phpstan-type SomeClass string
+ * @phpstan-type SomeClassAlias string
  */
 class AnotherClass
 {
 }
```

When using `@phpstan-import-type`, use the `as` keyword to rename the imported type alias:

```diff-php
 /**
- * @phpstan-import-type SomeClass from AnotherClass
+ * @phpstan-import-type SomeClass from AnotherClass as SomeClassAlias
  */
 class Foo
 {
 }
```
