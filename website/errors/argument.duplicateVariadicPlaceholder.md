---
title: "argument.duplicateVariadicPlaceholder"
shortDescription: "The variadic placeholder ... is used more than once in a partial function application."
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $a, string $b, float ...$c): bool
{
	return true;
}

$f = doFoo(?, ..., ...);
```

## Why is it reported?

In a [partial function application](https://wiki.php.net/rfc/partial_function_application_v2) (PHP 8.6+), the variadic placeholder `...` stands for all remaining parameters of the callee that were not bound or given a `?` placeholder. It can appear only once, and only as the last argument. Writing `...` twice is a compile-time error in PHP, so the file cannot be loaded at all.

## How to fix it

Remove the extra variadic placeholder:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $a, string $b, float ...$c): bool
 {
 	return true;
 }

-$f = doFoo(?, ..., ...);
+$f = doFoo(?, ...);
```
