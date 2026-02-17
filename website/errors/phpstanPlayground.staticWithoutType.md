---
title: "phpstanPlayground.staticWithoutType"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

function counter(): int
{
	static $count;
	$count++;

	return $count;
}
```

## Why is it reported?

A static variable is declared without a `@var` PHPDoc type annotation. Static variables persist their value across function calls, and without a type annotation PHPStan cannot reliably infer their type throughout the analysis.

This rule is specific to the [PHPStan playground](https://phpstan.org/try) and cannot be ignored.

## How to fix it

Add a `@var` PHPDoc tag above the `static` variable declaration:

```diff-php
 <?php declare(strict_types = 1);

 function counter(): int
 {
+	/** @var int $count */
 	static $count;
 	$count++;

 	return $count;
 }
```

When declaring multiple static variables in a single statement, specify the type for each variable by name:

```diff-php
 <?php declare(strict_types = 1);

 function example(): void
 {
+	/** @var int $a */
+	/** @var string $b */
 	static $a, $b;
 }
```
