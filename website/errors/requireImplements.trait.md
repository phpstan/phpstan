---
title: "requireImplements.trait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

trait SomeTrait
{
}

/**
 * @phpstan-require-implements SomeTrait
 */
trait MyTrait
{
}
```

## Why is it reported?

The `@phpstan-require-implements` PHPDoc tag contains a type that is a trait instead of an interface. This tag is used on traits to enforce that any class using the trait must implement a specific interface. Since traits cannot be implemented (only used), referencing a trait in `@phpstan-require-implements` is invalid.

## How to fix it

Replace the trait reference with an interface:

```diff-php
-/**
- * @phpstan-require-implements SomeTrait
- */
+/**
+ * @phpstan-require-implements SomeInterface
+ */
 trait MyTrait
 {
 }
```

If the goal is to ensure that classes using this trait also use another trait, there is no PHPDoc tag for that. Consider documenting the requirement in a comment or restructuring the code to use an interface instead.
