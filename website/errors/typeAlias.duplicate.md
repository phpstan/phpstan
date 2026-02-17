---
title: "typeAlias.duplicate"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class SomeClass
{
}

/**
 * @phpstan-import-type SomeClass from AnotherClass
 */
class Foo
{
}
```

## Why is it reported?

A type alias imported via `@phpstan-import-type` or defined via `@phpstan-type` conflicts with an existing class, interface, trait, or enum name in the current scope. In the example above, importing a type alias named `SomeClass` conflicts with the existing `SomeClass` class.

This ambiguity would make it unclear whether `SomeClass` refers to the actual class or the type alias.

## How to fix it

Use the `as` keyword to rename the imported type alias to avoid the conflict:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @phpstan-import-type SomeClass from AnotherClass
+ * @phpstan-import-type SomeClass from AnotherClass as SomeClassAlias
  */
 class Foo
 {
 }
```

Or choose a different name for the local type alias:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @phpstan-type MyAlias string
+ * @phpstan-type MyStringAlias string
  */
 class Foo
 {
 }
```
