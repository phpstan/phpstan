---
title: "argument.positionalAfterNamed"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

function greet(string $first, string $last): string
{
	return "$first $last";
}

greet(first: 'John', 'Doe');
```

## Why is it reported?

PHP does not allow positional arguments to follow named arguments in a function or method call. Once a named argument is used, all subsequent arguments must also be named. This is a compile-time error enforced by PHP itself.

In the example above, `'Doe'` is a positional argument that follows the named argument `first: 'John'`, which is not valid PHP syntax.

## How to fix it

Make all arguments after the first named argument also named:

```diff-php
 <?php declare(strict_types = 1);

 function greet(string $first, string $last): string
 {
 	return "$first $last";
 }

-greet(first: 'John', 'Doe');
+greet(first: 'John', last: 'Doe');
```

Alternatively, use only positional arguments:

```diff-php
 <?php declare(strict_types = 1);

 function greet(string $first, string $last): string
 {
 	return "$first $last";
 }

-greet(first: 'John', 'Doe');
+greet('John', 'Doe');
```
