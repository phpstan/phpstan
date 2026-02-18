---
title: "staticMethod.deprecated"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	/** @deprecated Use newMethod() instead */
	public static function oldMethod(): void
	{
	}
}

Foo::oldMethod();
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

A deprecated static method is being called. The method has been marked with `@deprecated` and is scheduled for removal or replacement. Continuing to use it means the code will eventually break when the method is removed.

## How to fix it

Use the recommended replacement method as indicated in the deprecation notice:

```diff-php
-Foo::oldMethod();
+Foo::newMethod();
```

If the calling code is itself deprecated, the error will not be reported. Mark the function or class as deprecated if it is part of a deprecation migration:

```diff-php
+/** @deprecated */
 function doFoo(): void
 {
 	Foo::oldMethod();
 }
```
