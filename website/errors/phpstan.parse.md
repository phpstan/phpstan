---
title: "phpstan.parse"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
	$x = ;
}
```

## Why is it reported?

PHPStan was unable to parse the PHP file due to a syntax error. The file contains invalid PHP code that the parser cannot process. This prevents PHPStan from analysing the file at all.

This error is not ignorable because PHPStan cannot perform any analysis on a file it cannot parse.

## How to fix it

Fix the syntax error in the PHP file. The error message includes the line number and a description of the parsing problem.

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): void
 {
-	$x = ;
+	$x = 1;
 }
```

If the file intentionally contains non-standard PHP syntax (for example, a template or code snippet), exclude it from PHPStan analysis using the [`excludePaths`](https://phpstan.org/user-guide/ignoring-errors#excluding-whole-files) configuration option.
