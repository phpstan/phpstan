---
title: "classConstant.final"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Base
{
	final public const VERSION = '1.0';
}

class Child extends Base
{
	public const VERSION = '2.0';
}
```

## Why is it reported?

A class constant is overriding a constant that has been declared as `final` in a parent class or interface. Final constants cannot be overridden by child classes. This is a PHP language constraint enforced at runtime.

In the example above, `Child::VERSION` attempts to override `Base::VERSION`, which is declared as `final`.

This error is also reported when a class declares a `final` constant that overrides a non-final constant from a used trait. Trait constants must keep the same finality when redeclared.

## How to fix it

Remove the overriding constant declaration from the child class:

```diff-php
 <?php declare(strict_types = 1);

 class Child extends Base
 {
-	public const VERSION = '2.0';
 }
```
