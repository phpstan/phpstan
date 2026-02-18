---
title: "property.deprecatedClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewConfig instead */
class OldConfig
{
	public string $value = '';
}

function doFoo(OldConfig $config): void
{
	echo $config->value;
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

A property is being accessed on an instance of a class that has been marked as `@deprecated`. Deprecated classes are planned for removal in a future version, and code should be migrated away from them. This can be reported both for accessing properties of deprecated classes and for using deprecated classes in native property type declarations.

## How to fix it

Replace the deprecated class with its recommended replacement:

```diff-php
-function doFoo(OldConfig $config): void
+function doFoo(NewConfig $config): void
 {
 	echo $config->value;
 }
```
