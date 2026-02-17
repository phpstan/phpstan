---
title: "requireImplements.nonObject"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @phpstan-require-implements int
 */
trait MyTrait // ERROR: PHPDoc tag @phpstan-require-implements contains non-object type int.
{
}
```

## Why is it reported?

The `@phpstan-require-implements` PHPDoc tag must reference an interface (object) type. Scalar types like `int`, `string`, `bool`, union types, or other non-object types cannot be implemented by a class and are therefore not valid in this tag.

## How to fix it

Replace the non-object type with an interface:

```diff-php
 <?php declare(strict_types = 1);

+interface Countable
+{
+	public function count(): int;
+}
+
 /**
- * @phpstan-require-implements int
+ * @phpstan-require-implements Countable
  */
 trait MyTrait
 {
 }
```

If the requirement is about a specific type constraint that is not an interface, consider whether `@phpstan-require-extends` (for classes) or another mechanism is more appropriate.
