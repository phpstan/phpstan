---
title: "booleanOr.leftNotBoolean"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$string = 'str';
$bool = true;

if ($string || $bool) {
	// ...
}
```

## Why is it reported?

The left side of the `||` (boolean OR) expression is not a boolean value. PHP will implicitly cast the non-boolean value to `bool` before evaluating the expression. This implicit type coercion can lead to unexpected behaviour depending on PHP's type juggling rules.

This rule is part of [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules) and enforces that only boolean values are used with the `||` operator, making the code's intent explicit.

In the example above, `$string` is of type `string`, not `bool`, so using it on the left side of `||` relies on PHP's loose type coercion.

## How to fix it

Use an explicit comparison to produce a boolean value:

```diff-php
 <?php declare(strict_types = 1);

 $string = 'str';
 $bool = true;

-if ($string || $bool) {
+if ($string !== '' || $bool) {
 	// ...
 }
```

Or convert the value to boolean before using it:

```diff-php
 <?php declare(strict_types = 1);

 $string = 'str';
 $bool = true;

-if ($string || $bool) {
+if (strlen($string) > 0 || $bool) {
 	// ...
 }
```
