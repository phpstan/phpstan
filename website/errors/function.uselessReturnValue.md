---
title: "function.uselessReturnValue"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$output = print_r(['name' => 'John', 'age' => 30]);
```

## Why is it reported?

The return value of a function like `print_r()`, `var_export()`, or `highlight_string()` is being used, but without passing `true` as the second parameter, these functions print their output directly and return a useless value (`true` for `print_r()`, `null` for `var_export()`).

In the example above, `print_r()` is called without the `$return` parameter set to `true`, so it prints the array directly to the output and returns `true`. Assigning or using this return value is meaningless.

## How to fix it

Pass `true` as the second parameter to return the output as a string instead of printing it:

```diff-php
 <?php declare(strict_types = 1);

-$output = print_r(['name' => 'John', 'age' => 30]);
+$output = print_r(['name' => 'John', 'age' => 30], true);
```

Or if the intent is only to print the value, do not use the return value:

```diff-php
 <?php declare(strict_types = 1);

-$output = print_r(['name' => 'John', 'age' => 30]);
+print_r(['name' => 'John', 'age' => 30]);
```
