---
title: "argument.unknown"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function foo(int $i, int $j): void
{
}

foo(i: 1, j: 2, z: 3);
```

## Why is it reported?

When using named arguments, the argument name must match a declared parameter name in the called function or method. In the example above, `foo()` declares parameters `$i` and `$j`, but the call passes a named argument `z` which does not correspond to any parameter. PHP throws an `Error` at runtime for unknown named arguments.

## How to fix it

Use the correct parameter name:

```diff-php
 <?php declare(strict_types = 1);

 function foo(int $i, int $j): void
 {
 }

-foo(i: 1, j: 2, z: 3);
+foo(i: 1, j: 2);
```

Or, if the extra argument is intentional, add a corresponding parameter to the function:

```diff-php
 <?php declare(strict_types = 1);

-function foo(int $i, int $j): void
+function foo(int $i, int $j, int $z = 0): void
 {
 }

 foo(i: 1, j: 2, z: 3);
```
