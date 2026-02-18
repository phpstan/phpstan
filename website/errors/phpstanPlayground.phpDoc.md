---
title: "phpstanPlayground.phpDoc"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

/*
 * @param string $name
 * @return int
 */
function doFoo(string $name): int
{
	return strlen($name);
}
```

## Why is it reported?

A comment contains PHPDoc tags (like `@param`, `@return`, `@var`, etc.) but uses `/*` instead of `/**` as the opening delimiter. PHP and PHPStan only recognize PHPDoc blocks that start with `/**`. A comment starting with `/*` is treated as a regular multi-line comment, and its type annotations are silently ignored.

This is a common typo that can cause PHPStan to miss type information that was intended to be provided.

## How to fix it

Change the comment opening from `/*` to `/**`:

```diff-php
-/*
+/**
  * @param string $name
  * @return int
  */
 function doFoo(string $name): int
 {
 	return strlen($name);
 }
```
