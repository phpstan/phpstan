---
title: "nullableType.void"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): ?void
{
}
```

## Why is it reported?

The type `void` cannot be used in a nullable type declaration (`?void`). The `void` return type means the function does not return any value. Making it nullable is contradictory and not allowed in PHP.

## How to fix it

Use `void` without the nullable modifier if the function does not return a value:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(): ?void
+function doFoo(): void
 {
 }
```

If the function may return `null`, use a proper return type like `?int`, `string|null`, or `mixed`.
