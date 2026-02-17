---
title: "booleanNot.exprNotBoolean"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$string = 'str';

if (!$string) {
	// ...
}
```

## Why is it reported?

The negation operator `!` is being applied to a non-boolean value. PHP will implicitly cast the value to a boolean using its type juggling rules before negating it. This implicit conversion can lead to unexpected behaviour, for example `!0` is `true`, `!''` is `true`, and `!'0'` is also `true`.

This rule is part of [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules) and enforces that only boolean values are used with the `!` operator, making the code's intent explicit.

In the example above, `$string` is of type `string`, not `bool`, so negating it relies on PHP's loose type coercion.

## How to fix it

Use an explicit comparison instead of relying on type juggling:

```diff-php
 <?php declare(strict_types = 1);

 $string = 'str';

-if (!$string) {
+if ($string === '') {
 	// ...
 }
```

Or cast the value to boolean explicitly before negating:

```diff-php
 <?php declare(strict_types = 1);

 $string = 'str';

-if (!$string) {
+if (!((bool) $string)) {
 	// ...
 }
```
