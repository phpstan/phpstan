---
title: "cast.unset"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

$value = (unset) $foo;
```

## Why is it reported?

The `(unset)` cast was removed in PHP 8.0. In earlier versions, `(unset) $expr` was equivalent to assigning `null`, but it served no practical purpose and was deprecated in PHP 7.2 before being removed entirely in PHP 8.0.

PHPStan reports this error when the configured PHP version is 8.0 or later.

## How to fix it

Replace the `(unset)` cast with a direct `null` assignment, since that was the only effect of the cast.

```diff-php
-$value = (unset) $foo;
+$value = null;
```
