---
title: "return.deprecatedEnum"
ignorable: true
---

This error is reported by `phpstan/phpstan-deprecation-rules`.

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewStatus instead */
enum OldStatus
{
	case Active;
	case Inactive;
}

function getStatus(): OldStatus
{
	return OldStatus::Active;
}
```

## Why is it reported?

The native return type declaration of a function or method references an enum that has been marked as deprecated. Using a deprecated enum in a return type means that callers will receive a type that is scheduled for removal, which will require changes in the future.

## How to fix it

Update the return type to use the non-deprecated replacement:

```diff-php
 <?php declare(strict_types = 1);

-function getStatus(): OldStatus
+function getStatus(): NewStatus
 {
-	return OldStatus::Active;
+	return NewStatus::Active;
 }
```
