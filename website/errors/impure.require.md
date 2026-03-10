---
title: "impure.require"
shortDescription: "Pure function uses require, which reads and executes a file."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @phpstan-pure */
function loadConfig(string $path): mixed
{
	return require $path;
}
```

## Why is it reported?

A function marked as `@phpstan-pure` must not have side effects and must depend only on its parameters. Using `require` or `require_once` inside a pure function is a side effect because it reads a file from disk and executes its contents, which can produce different results depending on external state.

## How to fix it

Remove the `@phpstan-pure` annotation if the function needs to use `require`:

```diff-php
 <?php declare(strict_types = 1);

-/** @phpstan-pure */
 function loadConfig(string $path): mixed
 {
 	return require $path;
 }
```

Alternatively, restructure the code so that the file loading happens outside the pure function, and pass the data as a parameter.
