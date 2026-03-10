---
title: "varTag.deprecatedInterface"
shortDescription: "@var PHPDoc tag references a deprecated interface."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewLogger instead */
interface OldLogger
{
}

/** @return mixed */
function getLogger()
{
	return null;
}

function doFoo(): void
{
	/** @var OldLogger $x */
	$x = getLogger();
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

An inline `@var` PHPDoc tag references an interface that has been marked as `@deprecated`. Deprecated interfaces are planned for removal in a future version, and type annotations should not rely on them.

## How to fix it

Update the `@var` tag to reference the replacement interface:

```diff-php
-	/** @var OldLogger $x */
+	/** @var NewLogger $x */
 	$x = getLogger();
```
