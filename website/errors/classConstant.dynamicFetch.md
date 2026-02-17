---
title: "classConstant.dynamicFetch"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public const BAR = 'bar';
}

$name = 'BAR';
echo Foo::{$name};
```

## Why is it reported?

Fetching class constants with a dynamic name (using a variable expression) is a feature only available in PHP 8.3 and later. If the project targets an earlier PHP version, this syntax is not supported and will cause a syntax error.

## How to fix it

If upgrading to PHP 8.3 or later is an option, update the [PHP version in the PHPStan configuration](https://phpstan.org/config-reference#phpversion).

Otherwise, use the `constant()` function to access the class constant dynamically:

```diff-php
 <?php declare(strict_types = 1);

 $name = 'BAR';
-echo Foo::{$name};
+echo constant(Foo::class . '::' . $name);
```
