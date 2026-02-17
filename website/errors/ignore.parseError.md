---
title: "ignore.parseError"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

$x = 1; // @phpstan-ignore this is not valid(

echo $x;
```

## Why is it reported?

The `@phpstan-ignore` inline comment contains a syntax error that prevents PHPStan from parsing the list of error identifiers. The ignore directive must follow a specific format: a comma-separated list of valid error identifiers, optionally with parenthesized descriptions.

## How to fix it

Correct the syntax of the `@phpstan-ignore` comment. Identifiers are dot-separated names, and optional descriptions go in parentheses with matching opening and closing brackets:

```diff-php
 <?php declare(strict_types = 1);

-$x = 1; // @phpstan-ignore this is not valid(
+$x = doSomething(); // @phpstan-ignore return.type (reason for ignoring)

 echo $x;
```

Multiple identifiers can be separated by commas:

```php
<?php declare(strict_types = 1);

$x = doSomething(); // @phpstan-ignore return.type, argument.type
```
