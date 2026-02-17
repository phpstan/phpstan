---
title: "return.unionTypeNotSupported"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function getValue(): int|string
{
	return 42;
}
```

## Why is it reported?

The function or method uses a native union type in its return type declaration, but the configured PHP version does not support union types. Native union types (using the `|` syntax, e.g., `int|string`) were introduced in PHP 8.0. Using them on earlier PHP versions will cause a syntax error.

## How to fix it

If you need to support PHP versions older than 8.0, remove the union type from the return type and use a PHPDoc annotation instead:

```diff-php
 <?php declare(strict_types = 1);

-function getValue(): int|string
+/**
+ * @return int|string
+ */
+function getValue()
 {
 	return 42;
 }
```

Alternatively, upgrade to PHP 8.0 or later to use native union types.
