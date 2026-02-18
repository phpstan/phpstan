---
title: "generics.existingTypeAlias"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @phpstan-type MyAlias string
 * @template MyAlias
 */
class Foo
{
}
```

## Why is it reported?

A template type parameter has the same name as an existing type alias defined with `@phpstan-type` or imported with `@phpstan-import-type`. This creates ambiguity because the name could refer to either the template type or the type alias.

## How to fix it

Rename the template type parameter to avoid the name conflict:

```diff-php
 /**
  * @phpstan-type MyAlias string
- * @template MyAlias
+ * @template T
  */
 class Foo
 {
 }
```

Or rename the type alias:

```diff-php
 /**
- * @phpstan-type MyAlias string
- * @template MyAlias
+ * @phpstan-type StringAlias string
+ * @template MyAlias
  */
 class Foo
 {
 }
```
