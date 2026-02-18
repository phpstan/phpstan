---
title: "logicalOr.rightNotBoolean"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$bool = true;
$string = 'str';

if ($bool or $string) {
	// ...
}
```

## Why is it reported?

This rule is part of [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules).

The right side of the `or` expression is not a boolean value. PHP will implicitly cast the non-boolean value to `bool` before evaluating the expression. This implicit type coercion can lead to unexpected behaviour depending on PHP's type juggling rules.

The `or` keyword is the low-precedence version of `||`. This identifier specifically covers the `or` keyword; for `||`, see [`booleanOr.rightNotBoolean`](/errors/booleanOr.rightNotBoolean).

In the example above, `$string` is of type `string`, not `bool`, so using it on the right side of `or` relies on PHP's loose type coercion.

## How to fix it

Use an explicit comparison to produce a boolean value:

```diff-php
 <?php declare(strict_types = 1);

 $bool = true;
 $string = 'str';

-if ($bool or $string) {
+if ($bool or $string !== '') {
 	// ...
 }
```
