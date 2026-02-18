---
title: "backtick.deprecated"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$output = `ls -la`;
```

## Why is it reported?

The backtick operator (`` ` ``) is deprecated in PHP 8.5. It is equivalent to `shell_exec()`, but its syntax makes it easy to confuse with single quotes and harder to spot in code. PHP 8.5 deprecates this operator in favour of the explicit `shell_exec()` function call.

## How to fix it

Replace the backtick operator with a `shell_exec()` call:

```diff-php
 <?php declare(strict_types = 1);

-$output = `ls -la`;
+$output = shell_exec('ls -la');
```
