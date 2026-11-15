---
title: "argument.duplicate"
shortDescription: "Same parameter receives a value more than once."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function foo(int $a, int $b, int $c = 3, int $d = 4): int
{
	return $a + $b + $c + $d;
}

foo(...[1, 2], b: 20);
```

## Why is it reported?

Named arguments in PHP must not overwrite a positional argument that was already passed. In the example above, the array unpacking `...[1, 2]` passes values to `$a` and `$b` positionally, and then `b: 20` attempts to pass `$b` again by name. PHP raises a fatal error at runtime in this case. This error is also reported when the same named parameter is explicitly provided more than once.

It is also reported when a closure created by a [partial function application](https://wiki.php.net/rfc/partial_function_application_v2) (PHP 8.6+) is called with a named argument for a parameter that the partial application already bound:

```php
<?php declare(strict_types = 1);

function doFoo(int $a, string $b, float $c, bool ...$d): string
{
	return '';
}

$f = doFoo(1, ?, ...);
$f('b', 1.0, a: true); // $a is already bound to 1
```

The parameter `$a` already received its value when the closure was created, so passing it again by name is a duplicate argument for the underlying function call.

## How to fix it

Remove the duplicate argument so each parameter receives exactly one value:

```diff-php
 <?php declare(strict_types = 1);

 function foo(int $a, int $b, int $c = 3, int $d = 4): int
 {
 	return $a + $b + $c + $d;
 }

-foo(...[1, 2], b: 20);
+foo(...[1, 2], d: 40);
```

For a partially applied closure, remove the named argument for the bound parameter, or pass the extra variadic argument under a different name:

```diff-php
-$f = doFoo(1, ?, ...);
-$f('b', 1.0, a: true);
+$f = doFoo(1, ?, ...);
+$f('b', 1.0, flag: true);
```
