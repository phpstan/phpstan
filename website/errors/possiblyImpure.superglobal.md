---
title: "possiblyImpure.superglobal"
shortDescription: "Pure function or method accesses a superglobal variable."
ignorable: true
feasible: false
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @phpstan-pure
 */
function getUserAgent(): string
{
	return $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
}
```

## Why is it reported?

This identifier is not reported in practice because superglobal access is always considered definitely impure, not just possibly impure. PHPStan reports [`impure.superglobal`](/error-identifiers/impure.superglobal) instead.

The function or method is marked as `@phpstan-pure` but accesses a superglobal variable such as `$_GET`, `$_POST`, `$_SERVER`, `$_SESSION`, `$_COOKIE`, `$_FILES`, `$_ENV`, or `$_REQUEST`. Superglobals represent external mutable state that can change between calls, so reading from or writing to them is a side effect. Pure functions must always return the same result for the same inputs.

## How to fix it

Pass the needed value as a parameter instead of accessing the superglobal directly:

```diff-php
 /**
  * @phpstan-pure
  */
-function getUserAgent(): string
+function getUserAgent(string $userAgent): string
 {
-	return $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
+	return $userAgent;
 }
```

Alternatively, remove the `@phpstan-pure` annotation if the function intentionally relies on superglobal state.
