---
title: "typeAlias.notFound"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

class Foo
{
    /** @phpstan-type MyType string */
}

/**
 * @phpstan-import-type UnknownAlias from Foo
 */
class Bar
{
}
```

## Why is it reported?

A `@phpstan-import-type` tag is trying to import a type alias that does not exist in the referenced class. The import specifies an alias name, but the target class does not define a type alias with that name via `@phpstan-type`.

In the example above, `Bar` attempts to import `UnknownAlias` from `Foo`, but `Foo` only defines a type alias called `MyType`.

## How to fix it

Use the correct type alias name that exists in the target class:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

 /**
- * @phpstan-import-type UnknownAlias from Foo
+ * @phpstan-import-type MyType from Foo
  */
 class Bar
 {
 }
```

Or define the missing type alias in the target class:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

 /**
  * @phpstan-type MyType string
+ * @phpstan-type UnknownAlias int
  */
 class Foo
 {
 }
```
