---
title: "class.disallowedSubtype"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @phpstan-sealed(AllowedChild)
 */
abstract class Base
{
}

class AllowedChild extends Base
{
}

class DisallowedChild extends Base
{
}
```

## Why is it reported?

The parent class or interface restricts which types are allowed to extend or implement it using the `@phpstan-sealed` PHPDoc tag (or the `AllowedSubTypes` interface). The class in question is not listed among the allowed subtypes.

In the example above, `Base` only allows `AllowedChild` as a subtype. `DisallowedChild` is not in the allowed list, so it is reported.

This mechanism is useful for simulating sealed classes or closed type hierarchies, ensuring that only a known set of types can extend or implement a given base type.

## How to fix it

Add the class to the list of allowed subtypes in the parent:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @phpstan-sealed(AllowedChild)
+ * @phpstan-sealed(AllowedChild, DisallowedChild)
  */
 abstract class Base
 {
 }
```

Or extend a different class that allows subtyping:

```diff-php
 <?php declare(strict_types = 1);

-class DisallowedChild extends Base
+class DisallowedChild extends AllowedChild
 {
 }
```
