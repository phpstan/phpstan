---
title: "new.deprecatedEnum"
shortDescription: "Instantiation of a deprecated enum."
ignorable: true
unlikely: true
---

## Code example

```php
<?php declare(strict_types = 1); // lint >= 8.1

/** @deprecated Use NewStatus instead */
enum OldStatus
{
	case Active;
}

$x = new OldStatus();
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

A deprecated enum is used in a `new` expression. Deprecated enums are planned for removal in a future version.

Note: triggering this identifier requires instantiating an enum, which PHP does not allow. PHPStan therefore always also reports a `new.enum` error, and in practice the deprecation identifier is not reported alongside it.

## How to fix it

Enums cannot be instantiated. Use enum cases directly instead:

```diff-php
-$x = new OldStatus();
+$x = NewStatus::Active;
```
