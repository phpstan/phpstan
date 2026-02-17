---
title: "interface.extendsDeprecatedInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewLoggerInterface instead */
interface OldLoggerInterface
{
	public function log(string $message): void;
}

interface MyLoggerInterface extends OldLoggerInterface // ERROR: Interface MyLoggerInterface extends deprecated interface OldLoggerInterface.
{
}
```

## Why is it reported?

This error is reported by `phpstan/phpstan-deprecation-rules`.

The interface being extended has been marked as deprecated with the `@deprecated` PHPDoc tag. Extending a deprecated interface means the child interface inherits a contract that may be removed or changed in a future version of the library. This can lead to breaking changes when the deprecated interface is eventually removed.

## How to fix it

Extend the replacement interface suggested in the deprecation notice:

```diff-php
 <?php declare(strict_types = 1);

-interface MyLoggerInterface extends OldLoggerInterface
+interface MyLoggerInterface extends NewLoggerInterface
 {
 }
```

If no replacement is available, define the required methods directly on the interface instead of extending the deprecated one:

```diff-php
 <?php declare(strict_types = 1);

-interface MyLoggerInterface extends OldLoggerInterface
+interface MyLoggerInterface
 {
+	public function log(string $message): void;
 }
```
