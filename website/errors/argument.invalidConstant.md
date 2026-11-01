---
title: "argument.invalidConstant"
shortDescription: "Passing a constant that is not valid for the given function parameter."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

json_encode([], SORT_REGULAR);
```

## Why is it reported?

The `SORT_REGULAR` constant is not a valid flag for the `$flags` parameter of `json_encode()`. Each PHP function that accepts predefined constants only works correctly with a specific set of them. Passing an unrelated constant results in unexpected behavior because the function interprets the underlying integer value differently than intended.

PHPStan maintains a map of allowed constants for parameters of built-in PHP functions and reports when a constant from a different domain is used.

The error is also reported when a constant that belongs to a different parameter of the same function is passed to the wrong position. For example, `STR_PAD_LEFT` is a valid value for the `$pad_type` parameter of `str_pad()`, so passing it as `$length` is most likely a mistake:

```php
<?php declare(strict_types = 1);

str_pad('', STR_PAD_LEFT);
```

Other built-in constants, like `PHP_INT_SIZE`, are not reported in that position because they are not tied to any parameter of the called function.

This check is only performed on PHP 8.0+ because it relies on named argument support to accurately map arguments to parameters.

## How to fix it

Use a constant that belongs to the correct function:

```diff-php
-json_encode([], SORT_REGULAR);
+json_encode([], JSON_PRETTY_PRINT);
```

Multiple valid constants can be combined with the `|` operator when the parameter accepts a bitmask:

```diff-php
-json_encode([], SORT_REGULAR);
+json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
```

When the constant was passed to the wrong parameter, move it to the parameter it belongs to:

```diff-php
-str_pad('', STR_PAD_LEFT);
+str_pad('', 10, ' ', STR_PAD_LEFT);
```
