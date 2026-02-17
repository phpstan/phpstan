---
title: "classConstant.deprecated"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	/** @deprecated Use NEW_VALUE instead */
	public const OLD_VALUE = 'old';

	public const NEW_VALUE = 'new';
}

echo Foo::OLD_VALUE;
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

The code accesses a class constant that has been marked with a `@deprecated` PHPDoc tag. Deprecated constants are scheduled for removal in a future version, and new code should not rely on them.

In the example above, the constant `OLD_VALUE` on class `Foo` is deprecated.

## How to fix it

Replace the usage with the recommended replacement constant:

```diff-php
 <?php declare(strict_types = 1);

-echo Foo::OLD_VALUE;
+echo Foo::NEW_VALUE;
```
