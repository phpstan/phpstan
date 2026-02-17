---
title: "typeAlias.deprecatedTrait"
ignorable: true
---

This error is reported by `phpstan/phpstan-deprecation-rules`.

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @deprecated Use NewLoggable instead
 */
trait OldLoggable
{
	public function log(string $message): void
	{
	}
}

/**
 * @phpstan-type LoggerType OldLoggable
 */
class Config
{
}
```

## Why is it reported?

A type alias defined via `@phpstan-type` references a trait that is marked as `@deprecated`. Using deprecated symbols in type aliases propagates the dependency on the deprecated code, making future migration harder. Additionally, traits are generally not valid types -- they should not be used in type positions.

## How to fix it

Update the type alias to reference the appropriate non-deprecated replacement type.

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @phpstan-type LoggerType OldLoggable
+ * @phpstan-type LoggerType NewLoggable
  */
 class Config
 {
 }
```
