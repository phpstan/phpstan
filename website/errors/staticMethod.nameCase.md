---
title: "staticMethod.nameCase"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public static function fooBar(): void
	{
	}
}

Foo::foobar();
```

## Why is it reported?

The static method is being called with a different letter case than how it is defined. While PHP method calls are case-insensitive and the code will work at runtime, using incorrect case is inconsistent and makes the code harder to read and maintain.

This error is only reported when the [`checkFunctionNameCase`](/config-reference#checkfunctionnamecase) configuration option is enabled.

## How to fix it

Match the case of the method name exactly as it is defined.

```diff-php
-Foo::foobar();
+Foo::fooBar();
```
