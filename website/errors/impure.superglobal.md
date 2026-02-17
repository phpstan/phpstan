---
title: "impure.superglobal"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @phpstan-pure */
function getParam(string $key): ?string
{
	return $_GET[$key] ?? null;
}
```

## Why is it reported?

A function marked as `@phpstan-pure` accesses a PHP superglobal variable (`$_GET`, `$_POST`, `$_SESSION`, `$_SERVER`, `$_COOKIE`, `$_FILES`, `$_ENV`, `$_REQUEST`, or `$GLOBALS`). Superglobal access is impure because it reads from global mutable state. Pure functions should only depend on their input parameters.

## How to fix it

Pass the needed value as a parameter instead:

```diff-php
 <?php declare(strict_types = 1);

-/** @phpstan-pure */
-function getParam(string $key): ?string
+/** @phpstan-pure */
+function getParam(array $params, string $key): ?string
 {
-	return $_GET[$key] ?? null;
+	return $params[$key] ?? null;
 }
```

Or remove the `@phpstan-pure` annotation if the function needs to access superglobals.
