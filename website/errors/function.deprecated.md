---
title: "function.deprecated"
ignorable: true
---

This error is reported by `phpstan/phpstan-deprecation-rules`.

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @deprecated Use newHelper() instead.
 */
function oldHelper(): void
{
}

oldHelper();
```

## Why is it reported?

The code calls a function that has been marked as deprecated via a `@deprecated` PHPDoc tag, or it calls a function like `ini_get()` or `ini_set()` with a deprecated INI option. Deprecated functions and options are planned for removal in a future PHP version and should no longer be used.

## How to fix it

Replace the call with the recommended alternative:

```diff-php
 <?php declare(strict_types = 1);

-oldHelper();
+newHelper();
```

For deprecated INI options, use the replacement option or remove the call:

```diff-php
 <?php declare(strict_types = 1);

-ini_set('assert.quiet_eval', '0');
+// assert.quiet_eval was removed in PHP 8.0
```
