---
title: "switch.type"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$value = 1;

switch ($value) {
	case 1:
		break;
	case 'test':
		break;
}
```

## Why is it reported?

This error is reported by the [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules) package.

A `switch` case condition has a type that is incompatible with the `switch` expression type. PHP's `switch` statement uses loose comparison (`==`) to match case values. When the case value type does not overlap with the switch expression type, the case can never match through strict typing logic, even if PHP's loose comparison might coerce the values.

In the example above, `$value` is `int` but the case `'test'` is a `string`. These types are incompatible and the case will never match the intended value.

## How to fix it

Ensure all case conditions match the type of the switch expression:

```diff-php
 $value = 1;

 switch ($value) {
 	case 1:
 		break;
-	case 'test':
+	case 2:
 		break;
 }
```

If the switch expression can have multiple types, adjust its type declaration to reflect that:

```diff-php
-$value = 1;
+/** @var int|string $value */

 switch ($value) {
 	case 1:
 		break;
 	case 'test':
 		break;
 }
```
