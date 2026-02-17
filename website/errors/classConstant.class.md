---
title: "classConstant.class"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public const FOO = 'foo';
	public const CLASS = 'bar';
}
```

## Why is it reported?

The name `class` is reserved for the special `::class` constant syntax in PHP, which returns the fully qualified class name as a string (e.g. `Foo::class` evaluates to `'Foo'`). Defining a class constant named `class` (case-insensitive) would conflict with this built-in language feature and is not allowed.

## How to fix it

Rename the class constant to something other than `class`:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	public const FOO = 'foo';
-	public const CLASS = 'bar';
+	public const CLASS_NAME = 'bar';
 }
```
