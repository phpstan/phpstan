---
title: "class.extendsInternalClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// Assuming SomeLibrary\InternalBase is marked @internal
class MyClass extends \SomeLibrary\InternalBase
{
}
```

## Why is it reported?

The class extends another class that is marked as `@internal` by its declaring library. Internal classes are implementation details not meant to be extended by external code. The library may change, rename, or remove internal classes without notice, which would break any code that extends them.

## How to fix it

Extend a public base class provided by the library instead:

```diff-php
 <?php declare(strict_types = 1);

-class MyClass extends \SomeLibrary\InternalBase
+class MyClass extends \SomeLibrary\PublicBase
 {
 }
```

Or implement a public interface instead of extending the internal class:

```diff-php
 <?php declare(strict_types = 1);

-class MyClass extends \SomeLibrary\InternalBase
+class MyClass implements \SomeLibrary\PublicInterface
 {
 }
```
