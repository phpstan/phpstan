---
title: "while.condNotBoolean"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$someString = 'hello';
while ($someString) {
	$someString = '';
}
```

## Why is it reported?

This error is reported by [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules).

The condition of the `while` loop is not a boolean value. PHP will implicitly convert the value to a boolean using its loose truthiness rules, which can lead to unexpected behavior. For example, `0`, `''`, `'0'`, `[]`, and `null` are all falsy, while other values of the same types are truthy. Relying on implicit type coercion in conditions makes the code harder to reason about.

## How to fix it

Use an explicit boolean comparison:

```diff-php
 $someString = 'hello';
-while ($someString) {
+while ($someString !== '') {
 	$someString = '';
 }
```

Or cast to boolean explicitly to make the intent clear:

```diff-php
 $someString = 'hello';
-while ($someString) {
+while ((bool) $someString) {
 	$someString = '';
 }
```
