---
title: "assert.alreadyNarrowedType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @phpstan-assert int $i
 */
function assertInt(int $i): void
{
}
```

## Why is it reported?

The `@phpstan-assert` tag declares that the function asserts a certain type for a parameter, but the asserted type does not narrow down the parameter's type any further. In this example, the parameter `$i` is already typed as `int` via the native type declaration, so asserting that it is `int` provides no additional type information. The assertion is redundant because the type is already as narrow as or narrower than the asserted type.

## How to fix it

Either assert a more specific type that actually narrows the parameter type:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @phpstan-assert int $i
+ * @phpstan-assert positive-int $i
  */
-function assertInt(int $i): void
+function assertPositiveInt(int $i): void
 {
 }
```

Or remove the redundant assertion if no narrowing is needed:

```diff-php
 <?php declare(strict_types = 1);

-/**
- * @phpstan-assert int $i
- */
 function assertInt(int $i): void
 {
 }
```

Or widen the parameter type if the assertion is intended to accept a broader input:

```diff-php
 <?php declare(strict_types = 1);

 /**
  * @phpstan-assert int $i
  */
-function assertInt(int $i): void
+function assertInt(mixed $i): void
 {
 }
```
