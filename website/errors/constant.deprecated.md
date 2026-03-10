---
title: "constant.deprecated"
shortDescription: "Usage of a deprecated global constant."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NEW_LIMIT instead */
const OLD_LIMIT = 100;

echo OLD_LIMIT;
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

The code uses a global constant that has been marked as deprecated via a `@deprecated` PHPDoc tag. Deprecated constants are planned for removal in a future version and should no longer be relied upon.

## How to fix it

Replace the deprecated constant with its recommended replacement:

```diff-php
-echo OLD_LIMIT;
+echo NEW_LIMIT;
```
