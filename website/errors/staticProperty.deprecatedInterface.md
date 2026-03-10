---
title: "staticProperty.deprecatedInterface"
shortDescription: "Static property access on a deprecated interface."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewConfig instead */
interface OldConfig
{
}

echo OldConfig::$value;
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

A static property is accessed directly on an interface that has been marked as `@deprecated`. Deprecated interfaces are planned for removal in a future version, and code should not access static properties on them.

## How to fix it

Use the recommended replacement interface or access the property through a non-deprecated type:

```diff-php
-echo OldConfig::$value;
+echo NewConfig::$value;
```
