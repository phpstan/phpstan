---
title: "property.override"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public int $foo;
}

class Bar extends Foo
{
	#[\Override]
	public int $foo;

	#[\Override]
	public int $bar;
}
```

## Why is it reported?

The `#[\Override]` attribute on a property declares that the property is intended to override a property from a parent class. If the parent class does not have a property with the same name, the attribute is incorrect. This prevents situations where a parent property is renamed or removed but the child class still claims to override it.

In the example above, `Bar::$foo` correctly overrides `Foo::$foo`, but `Bar::$bar` has the `#[\Override]` attribute even though `Foo` does not have a `$bar` property, which is an error.

This feature requires PHP 8.5 or later for the `#[\Override]` attribute on properties.

## How to fix it

If the property is not meant to override a parent property, remove the `#[\Override]` attribute:

```diff-php
 <?php declare(strict_types = 1);

 class Bar extends Foo
 {
 	#[\Override]
 	public int $foo;

-	#[\Override]
 	public int $bar;
 }
```

If the property should override a parent property, verify the parent class actually declares a property with the same name, or fix the property name to match:

```diff-php
 <?php declare(strict_types = 1);

 class Bar extends Foo
 {
 	#[\Override]
 	public int $foo;

 	#[\Override]
-	public int $bar;
+	public int $baz; // if Foo has $baz
 }
```
