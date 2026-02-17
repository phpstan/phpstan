---
title: "impure.require"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

#[\Pure]
function loadConfig(string $path): array
{
	return require $path;
}
```

## Why is it reported?

A function marked with the `#[\Pure]` attribute must not have side effects and must depend only on its parameters. Using `require` or `include` inside a pure function is a side effect because it reads a file from disk and executes its contents, which can produce different results depending on external state.

## How to fix it

Remove the `#[\Pure]` attribute if the function needs to use `require` or `include`, or restructure the code so that the file loading happens outside the pure function.

```diff-php
 <?php declare(strict_types = 1);

-#[\Pure]
 function loadConfig(string $path): array
 {
 	return require $path;
 }
```
