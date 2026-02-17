---
title: "variable.implicitArray"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function collectItems(): void
{
	$items[] = 'first';
	$items[] = 'second';
}
```

## Why is it reported?

This error is reported by the [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules) extension.

An array is being implicitly created by assigning to an array offset on a variable that has not been defined. In the example above, `$items` does not exist before the first assignment, so PHP implicitly creates an empty array. While PHP allows this behaviour, it can mask bugs where a variable was expected to already exist, or where a variable name was misspelled.

## How to fix it

Explicitly initialize the variable as an array before appending to it:

```diff-php
 <?php declare(strict_types = 1);

 function collectItems(): void
 {
+	$items = [];
 	$items[] = 'first';
 	$items[] = 'second';
 }
```
