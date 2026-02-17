---
title: "constant.deprecated"
ignorable: true
---

This error is reported by `phpstan/phpstan-deprecation-rules`.

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @deprecated Use NEW_LIMIT instead.
 */
define('OLD_LIMIT', 100);

echo OLD_LIMIT;
```

## Why is it reported?

The code uses a constant that has been marked as deprecated via a `@deprecated` PHPDoc tag. Deprecated constants are planned for removal in a future version and should no longer be relied upon.

## How to fix it

Replace the deprecated constant with its recommended replacement:

```diff-php
 <?php declare(strict_types = 1);

-echo OLD_LIMIT;
+echo NEW_LIMIT;
```
