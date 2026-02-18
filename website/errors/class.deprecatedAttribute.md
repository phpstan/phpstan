---
title: "class.deprecatedAttribute"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

#[\Deprecated]
class Foo
{
}
```

## Why is it reported?

The PHP 8.4 `#[\Deprecated]` attribute is designed for functions, methods, and class constants. It cannot be applied to classes, interfaces, or enums. To mark a class as deprecated, use the `@deprecated` PHPDoc tag instead. Using the `#[\Deprecated]` attribute on a class has no effect at runtime and indicates a misunderstanding of the attribute's intended usage.

On PHP 8.5, the `#[\Deprecated]` attribute can also be used on traits and constants.

## How to fix it

Replace the `#[\Deprecated]` attribute with a `@deprecated` PHPDoc tag:

```diff-php
 <?php declare(strict_types = 1);

-#[\Deprecated]
+/** @deprecated Use Bar instead */
 class Foo
 {
 }
```
