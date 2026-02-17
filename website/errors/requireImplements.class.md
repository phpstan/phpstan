---
title: "requireImplements.class"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class SomeClass
{
}

/**
 * @phpstan-require-implements SomeClass
 */
trait MyTrait // ERROR: PHPDoc tag @phpstan-require-implements cannot contain non-interface type SomeClass.
{
}
```

## Why is it reported?

The `@phpstan-require-implements` PHPDoc tag is used on traits to declare that any class using the trait must implement a specific interface. However, the tag references a class instead of an interface. Since classes are not "implemented" but "extended", this is not a valid use of `@phpstan-require-implements`.

## How to fix it

If the trait should require that the using class extends a specific class, use `@phpstan-require-extends` instead:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @phpstan-require-implements SomeClass
+ * @phpstan-require-extends SomeClass
  */
 trait MyTrait
 {
 }
```

If the trait should require that the using class implements a specific interface, reference an interface:

```diff-php
 <?php declare(strict_types = 1);

+interface SomeInterface
+{
+}
+
 /**
- * @phpstan-require-implements SomeClass
+ * @phpstan-require-implements SomeInterface
  */
 trait MyTrait
 {
 }
```
