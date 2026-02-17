---
title: "nullableType.mixed"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(?mixed $value): void
{
}
```

## Why is it reported?

The type `mixed` cannot be used in a nullable type declaration (`?mixed`). The `mixed` type already includes `null` (it represents any type including `null`), so making it nullable is redundant and not allowed in PHP.

## How to fix it

Use `mixed` without the nullable modifier:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(?mixed $value): void
+function doFoo(mixed $value): void
 {
 }
```
