---
title: "possiblyImpure.require"
shortDescription: "Pure function uses require which can execute arbitrary code."
ignorable: true
feasible: false
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

This identifier is not reported in practice because `require` is always considered definitely impure, not just possibly impure. PHPStan reports [`impure.require`](/error-identifiers/impure.require) instead.

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
