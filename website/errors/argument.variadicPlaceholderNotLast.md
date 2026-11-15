---
title: "argument.variadicPlaceholderNotLast"
shortDescription: "An argument follows the variadic placeholder ... in a partial function application."
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $a, string $b, float ...$c): bool
{
	return true;
}

$f = doFoo(..., ?);
```

## Why is it reported?

In a [partial function application](https://wiki.php.net/rfc/partial_function_application_v2) (PHP 8.6+), the variadic placeholder `...` stands for all remaining parameters of the callee. Because it covers everything that comes after it, it has to be the last argument in the call. Placing a `?` placeholder or a bound argument after `...` is a compile-time error in PHP, so the file cannot be loaded at all.

## How to fix it

Move the variadic placeholder to the end of the argument list:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $a, string $b, float ...$c): bool
 {
 	return true;
 }

-$f = doFoo(..., ?);
+$f = doFoo(?, ...);
```

To bind a later parameter while leaving earlier ones open, use a named argument before the variadic placeholder:

```diff-php
-$f = doFoo(..., 'b');
+$f = doFoo(b: 'b', ...);
```
