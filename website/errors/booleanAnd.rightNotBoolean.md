---
title: "booleanAnd.rightNotBoolean"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$flag = true;
$string = 'hello';

if ($flag && $string) {
	// ...
}
```

## Why is it reported?

The right side of the `&&` (boolean AND) expression is not a boolean value. PHP will implicitly cast the non-boolean value to `bool` before evaluating the expression. This implicit type coercion can lead to unexpected behaviour depending on PHP's type juggling rules.

This rule is part of [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules) and enforces that only boolean values are used with the `&&` operator, making the code's intent explicit.

In the example above, `$string` is of type `string`, not `bool`, so using it on the right side of `&&` relies on PHP's loose type coercion.

## How to fix it

Use an explicit comparison to produce a boolean value:

```diff-php
 <?php declare(strict_types = 1);

 $flag = true;
 $string = 'hello';

-if ($flag && $string) {
+if ($flag && $string !== '') {
 	// ...
 }
```

Or convert the value to boolean with a meaningful comparison:

```diff-php
 <?php declare(strict_types = 1);

 $flag = true;
 $string = 'hello';

-if ($flag && $string) {
+if ($flag && strlen($string) > 0) {
 	// ...
 }
```
