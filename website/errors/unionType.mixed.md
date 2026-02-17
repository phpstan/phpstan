---
title: "unionType.mixed"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(mixed|string $value): void
{
}
```

## Why is it reported?

The `mixed` type cannot be part of a union type declaration in PHP. The `mixed` type already represents all possible types (`int|string|float|bool|array|object|null|resource|callable`), so combining it with other types in a union is redundant and not allowed by the PHP language. This is a compile-time error in PHP 8.0 and later.

The same applies to `?mixed` (nullable mixed), because `mixed` already includes `null`.

## How to fix it

If the intention is to accept any type, use `mixed` alone:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(mixed|string $value): void
+function doFoo(mixed $value): void
 {
 }
```

If the intention is to accept only specific types, list them explicitly without `mixed`:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(mixed|string $value): void
+function doFoo(string|int $value): void
 {
 }
```

For nullable mixed, remove the `?` since `mixed` already includes `null`:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(?mixed $value): void
+function doFoo(mixed $value): void
 {
 }
```
