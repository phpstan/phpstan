---
title: "possiblyImpure.require"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @phpstan-pure */
function loadConfig(): array
{
	require 'config.php';
	return $config;
}
```

## Why is it reported?

A function marked as `@phpstan-pure` contains a `require` or `require_once` statement. Including files can have side effects (such as defining functions, classes, or executing code), which makes the operation possibly impure.

## How to fix it

Remove the `require` from the pure function:

```diff-php
 <?php declare(strict_types = 1);

-/** @phpstan-pure */
 function loadConfig(): array
 {
 	require 'config.php';
 	return $config;
 }
```
