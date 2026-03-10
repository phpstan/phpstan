---
title: "class.extendsInternalClass"
shortDescription: "Class extends a class marked as @internal."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	class InternalBase {}
}

namespace App {
	class MyClass extends \Vendor\InternalBase {}
}
```

## Why is it reported?

The class extends another class that is marked as `@internal` by its declaring library. Internal classes are implementation details not meant to be extended by external code. The library may change, rename, or remove internal classes without notice, which would break any code that extends them.

## How to fix it

Extend a public base class provided by the library instead:

```diff-php
-class MyClass extends \Vendor\InternalBase {}
+class MyClass extends \Vendor\PublicBase {}
```

Or implement a public interface instead of extending the internal class:

```diff-php
-class MyClass extends \Vendor\InternalBase {}
+class MyClass implements \Vendor\PublicInterface {}
```
