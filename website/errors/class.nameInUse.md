---
title: "class.nameInUse"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

namespace SomeNamespace;

use SomeOtherNamespace\Foo;

class Foo
{
}
```

## Why is it reported?

A class is being declared with a name that is already in use within the same namespace. This happens when a `use` statement imports a class with the same name as a class being declared in the current file. PHP cannot resolve which `Foo` is intended, resulting in a fatal error at runtime.

In the example above, `Foo` is imported via `use SomeOtherNamespace\Foo` and then a class named `Foo` is declared in the `SomeNamespace` namespace. Both refer to the short name `Foo`, creating a conflict.

## How to fix it

Rename the local class to avoid the conflict:

```diff-php
 <?php declare(strict_types = 1);

 namespace SomeNamespace;

 use SomeOtherNamespace\Foo;

-class Foo
+class MyFoo
 {
 }
```

Or use an alias for the imported class:

```diff-php
 <?php declare(strict_types = 1);

 namespace SomeNamespace;

-use SomeOtherNamespace\Foo;
+use SomeOtherNamespace\Foo as OtherFoo;

 class Foo
 {
 }
```

Or remove the `use` statement if the imported class is not needed:

```diff-php
 <?php declare(strict_types = 1);

 namespace SomeNamespace;

-use SomeOtherNamespace\Foo;
-
 class Foo
 {
 }
```
