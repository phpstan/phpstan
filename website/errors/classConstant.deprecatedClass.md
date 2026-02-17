---
title: "classConstant.deprecatedClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewConfig instead */
class OldConfig
{
	public const VERSION = '1.0';
}

$version = OldConfig::VERSION;
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

A class constant is being accessed on a class that has been marked as `@deprecated`. Accessing constants on deprecated classes creates dependencies on classes that are planned for removal.

In the example above, the constant `VERSION` is fetched from the deprecated class `OldConfig`.

## How to fix it

Access the constant from the recommended replacement class instead:

```diff-php
 <?php declare(strict_types = 1);

-$version = OldConfig::VERSION;
+$version = NewConfig::VERSION;
```
