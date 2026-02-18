---
title: "parameter.variadicNotLast"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(string ...$names, int $count): void
{
}
```

## Why is it reported?

A variadic parameter (`...`) is not the last parameter in the function declaration. PHP requires the variadic parameter to always be the last one, because it collects all remaining arguments. Placing it before other parameters is a syntax error.

## How to fix it

Move the variadic parameter to the end of the parameter list:

```diff-php
-function doFoo(string ...$names, int $count): void
+function doFoo(int $count, string ...$names): void
 {
 }
```
