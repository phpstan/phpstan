---
title: "trait.deprecatedAttribute"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

#[\Deprecated]
trait MyTrait
{
}
```

## Why is it reported?

The native `#[\Deprecated]` attribute is applied to a trait, but the current PHP version does not support this. The `#[\Deprecated]` attribute can only be used with traits starting from PHP 8.5.

On PHP versions earlier than 8.5, the attribute has no effect on traits and PHPStan reports this as an error.

This error is non-ignorable because it indicates incorrect usage of a language feature.

## How to fix it

If running PHP 8.5 or later, this error will not be reported.

For earlier PHP versions, use the `@deprecated` PHPDoc tag instead:

```diff-php
+/** @deprecated */
-#[\Deprecated]
 trait MyTrait
 {
 }
```

Alternatively, upgrade to PHP 8.5 or later to use the native attribute syntax.
