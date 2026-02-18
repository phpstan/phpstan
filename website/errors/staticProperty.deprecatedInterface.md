---
title: "staticProperty.deprecatedInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewConfig instead */
interface OldConfig
{
}

class AppConfig implements OldConfig
{
	public static string $value = 'default';
}

AppConfig::$value;
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

A static property is accessed on a type that involves a deprecated interface. The interface has been marked with `@deprecated` and is scheduled for removal or replacement. This error can be triggered when accessing a static property on a class that is typed through a deprecated interface, or when the declaring class of the property is a deprecated interface.

## How to fix it

Use the recommended replacement interface or access the property through a non-deprecated type:

```diff-php
-/** @deprecated Use NewConfig instead */
-interface OldConfig
-{
-}
+interface NewConfig
+{
+}
```

If the calling code is itself deprecated, the error will not be reported. Mark the function or class as deprecated if it is part of a deprecation migration:

```diff-php
+/** @deprecated */
 function doFoo(): void
 {
 	AppConfig::$value;
 }
```
