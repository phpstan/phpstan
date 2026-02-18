---
title: "staticProperty.deprecated"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	/** @deprecated Use getInstance() instead */
	public static $instance;
}

Foo::$instance;
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

A static property marked as `@deprecated` is being accessed. Deprecated properties are scheduled for removal or replacement. Continuing to use them means the code will break when the property is removed.

## How to fix it

Use the recommended replacement as indicated in the deprecation notice:

```diff-php
-Foo::$instance;
+Foo::getInstance();
```

If the calling code is itself deprecated, the error will not be reported. Mark the function or class as deprecated if it is part of a deprecation migration:

```diff-php
+/** @deprecated */
 function doFoo(): void
 {
 	Foo::$instance;
 }
```
