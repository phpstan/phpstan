---
title: "enum.generic"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @template T
 */
enum Status
{
    case Active;
    case Inactive;
}
```

## Why is it reported?

PHP enums cannot be generic. Unlike classes and interfaces, enums do not support `@template` type parameters because enum cases are singleton values and cannot be parameterized with different types.

## How to fix it

Remove the `@template` tag from the enum. If you need generic behaviour, consider using an interface or a class instead:

```diff-php
 <?php declare(strict_types = 1);

-/**
- * @template T
- */
 enum Status
 {
     case Active;
     case Inactive;
 }
```
