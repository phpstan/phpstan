---
title: "argument.duplicate"
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
