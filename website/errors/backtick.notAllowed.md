---
title: "backtick.notAllowed"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$output = `ls -la`;
```

## Why is it reported?

The backtick operator (`` ` ``) in PHP executes shell commands, which is equivalent to calling `shell_exec()`. This rule from the `phpstan-strict-rules` package disallows the backtick operator because it obscures the fact that a shell command is being executed, making code harder to review for security issues. The backtick syntax is less explicit than the function call equivalent and can be easily overlooked during code review.

This rule is provided by the [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules) package.

## How to fix it

Replace the backtick operator with an explicit `shell_exec()` call:

```diff-php
 <?php declare(strict_types = 1);

-$output = `ls -la`;
+$output = shell_exec('ls -la');
```
