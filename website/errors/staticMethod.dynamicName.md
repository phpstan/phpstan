---
title: "staticMethod.dynamicName"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public static function doBar(): void
	{
	}
}

$method = 'doBar';
Foo::$method();
```

## Why is it reported?

This error is reported by [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules).

A static method is called using a variable method name (`Foo::$method()`). Variable static method calls make the code harder to analyze and reason about because the actual method being called is only known at runtime. This prevents static analysis from verifying that the method exists and that the correct arguments are passed.

## How to fix it

Call the method directly by its name:

```diff-php
-$method = 'doBar';
-Foo::$method();
+Foo::doBar();
```

If dynamic dispatch is needed, consider using a match expression or a strategy pattern:

```diff-php
-$method = 'doBar';
-Foo::$method();
+match ($action) {
+	'bar' => Foo::doBar(),
+	'baz' => Foo::doBaz(),
+};
```
