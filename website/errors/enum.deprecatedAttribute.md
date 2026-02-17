---
title: "enum.deprecatedAttribute"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

#[\Deprecated]
enum Status
{
    case Active;
    case Inactive;
}
```

## Why is it reported?

The PHP `#[\Deprecated]` attribute (introduced in PHP 8.4) cannot be used on enums. This attribute is designed for functions, methods, and class constants, but PHP does not support marking entire enums as deprecated using this native attribute. Attempting to use it on an enum is invalid.

## How to fix it

Remove the `#[\Deprecated]` attribute from the enum. To mark an enum as deprecated, use the `@deprecated` PHPDoc tag instead:

```diff-php
 <?php declare(strict_types = 1);

-#[\Deprecated]
+/** @deprecated Use NewStatus instead */
 enum Status
 {
     case Active;
     case Inactive;
 }
```
