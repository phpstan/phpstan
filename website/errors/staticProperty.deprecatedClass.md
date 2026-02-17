---
title: "staticProperty.deprecatedClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewConfig instead */
class OldConfig
{
	public static string $value = 'default';
}

echo OldConfig::$value;
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

A static property is accessed on a class that is marked as `@deprecated`. Deprecated classes are planned for removal in a future version, and code should not rely on them.

## How to fix it

Use the recommended replacement class:

```diff-php
 <?php declare(strict_types = 1);

-echo OldConfig::$value;
+echo NewConfig::$value;
```
