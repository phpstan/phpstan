---
title: "classConstant.inTrait"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

trait FooBar
{
	const FOO = 'foo';
	public const BAR = 'bar';
}

class Consumer
{
	use FooBar;
}
```

## Why is it reported?

Constants in traits are only supported starting from PHP 8.2. If the project targets an earlier PHP version, declaring a constant inside a trait will cause a syntax error at runtime. This error is reported when PHPStan detects that the configured PHP version is lower than 8.2.

## How to fix it

Move the constants to the class that uses the trait, or to an interface:

```diff-php
 <?php declare(strict_types = 1);

 trait FooBar
 {
-	const FOO = 'foo';
-	public const BAR = 'bar';
 }

 class Consumer
 {
 	use FooBar;
+
+	const FOO = 'foo';
+	public const BAR = 'bar';
 }
```

Or upgrade the minimum PHP version of the project to 8.2 or later.
