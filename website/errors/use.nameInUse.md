---
title: "use.nameInUse"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

use SomeOtherNamespace\Foo;

class Foo
{
}
```

## Why is it reported?

A `use` import introduces a name that is already taken by another declaration in the same namespace scope. In the example, the `use` statement imports `SomeOtherNamespace\Foo` as `Foo`, but there is also a class named `Foo` declared in the same namespace. This creates a naming conflict that PHP cannot resolve.

This is a PHP compile-time error -- having two definitions for the same name in the same scope is not allowed.

## How to fix it

Use an alias for the imported name to avoid the conflict:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use SomeOtherNamespace\Foo;
+use SomeOtherNamespace\Foo as OtherFoo;

 class Foo
 {
 }
```

Or remove the conflicting `use` statement if it is not needed:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use SomeOtherNamespace\Foo;
-
 class Foo
 {
 }
```
