---
title: "use.nameInUse"
shortDescription: "Imported name conflicts with an existing declaration in the same scope."
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

class Foo
{
}

use SomeOtherNamespace\Foo;
```

## Why is it reported?

A `use` import introduces a name that is already taken by another declaration in the same namespace scope. In the example, the class `Foo` is declared in the `App` namespace, and then a `use` statement tries to import `SomeOtherNamespace\Foo` as `Foo`. This creates a naming conflict that PHP cannot resolve.

This is a PHP compile-time error -- having two definitions for the same name in the same scope is not allowed.

## How to fix it

Use an alias for the imported name to avoid the conflict:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

 class Foo
 {
 }

-use SomeOtherNamespace\Foo;
+use SomeOtherNamespace\Foo as OtherFoo;
```

Or remove the conflicting `use` statement if it is not needed:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

 class Foo
 {
 }
-
-use SomeOtherNamespace\Foo;
```
