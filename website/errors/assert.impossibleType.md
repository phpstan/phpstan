---
title: "assert.impossibleType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @phpstan-assert string $i
 */
function assertString(int $i): void
{
}
```

## Why is it reported?

The `@phpstan-assert` PHPDoc tag declares an assertion that can never be satisfied given the parameter's type. The asserted type and the parameter type are completely incompatible, so the assertion would always fail.

In the example above, the parameter `$i` has a native type of `int`, and the `@phpstan-assert` tag asserts it is `string`. Since `int` and `string` are disjoint types, this assertion can never succeed.

## How to fix it

Widen the parameter type so the assertion can meaningfully narrow it:

```diff-php
 <?php declare(strict_types = 1);

 /**
  * @phpstan-assert string $i
  */
-function assertString(int $i): void
+function assertString(mixed $i): void
 {
 }
```

Or change the asserted type to one compatible with the parameter:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @phpstan-assert string $i
+ * @phpstan-assert positive-int $i
  */
-function assertString(int $i): void
+function assertPositiveInt(int $i): void
 {
 }
```
