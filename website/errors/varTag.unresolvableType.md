---
title: "varTag.unresolvableType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @var string&int $value */
$value = getValue();
```

## Why is it reported?

The type used in the `@var` PHPDoc tag cannot be resolved to a valid type. This usually happens when the type is an impossible intersection (like `string&int` which can never exist), references an undefined type, or contains a syntax error. PHPStan cannot determine what the type annotation means, so it reports an error.

In the example above, `string&int` is an intersection type that can never be satisfied because no value can be both a `string` and an `int` at the same time.

## How to fix it

Use a valid, resolvable type in the `@var` tag:

```diff-php
 <?php declare(strict_types = 1);

-/** @var string&int $value */
+/** @var string $value */
 $value = getValue();
```

If the intent is to express a union type, use `|` instead of `&`:

```diff-php
-/** @var string&int $value */
+/** @var string|int $value */
```
