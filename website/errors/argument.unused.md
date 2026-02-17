---
title: "argument.unused"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

define('MY_CONST', 'value', true);
```

## Why is it reported?

The third argument `$case_insensitive` of `define()` is ignored since PHP 8.0. Declaration of case-insensitive constants was deprecated in PHP 7.3 and the parameter was removed in PHP 8.0. Passing this argument has no effect and is silently ignored.

## How to fix it

Remove the third argument:

```diff-php
 <?php declare(strict_types = 1);

-define('MY_CONST', 'value', true);
+define('MY_CONST', 'value');
```
