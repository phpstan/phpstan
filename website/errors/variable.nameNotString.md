---
title: "variable.nameNotString"
shortDescription: "Variable variable name in $$name is not a string."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(array $name): void
{
	echo $$name;
}
```

## Why is it reported?

When using a variable variable with the `$$name` syntax, the `$name` expression must produce a string. PHP casts the name to string at runtime, so a `string`, an `int`, or an object implementing `Stringable` are all accepted. Values that cannot be cast to a string — such as an `array` or a plain `object` — are invalid and result in an error. In the example above, `$name` is an `array`, which cannot be used as a variable name.

This check is currently part of [Bleeding Edge](/blog/what-is-bleeding-edge).

## How to fix it

Ensure the variable variable name can be cast to a string:

```diff-php
-function doFoo(array $name): void
+function doFoo(string $name): void
 {
 	echo $$name;
 }
```

Variable variables are hard to analyse and easy to misuse. When the set of names is known, an array is usually a clearer and safer alternative:

```diff-php
-function doFoo(string $name): void
+function doFoo(array $values, string $name): void
 {
-	echo $$name;
+	echo $values[$name];
 }
```
