---
title: "interface.deprecatedAttribute"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

#[\Deprecated]
interface LoggerInterface
{
	public function log(string $message): void;
}
```

## Why is it reported?

The PHP `#[\Deprecated]` attribute (introduced in PHP 8.4) cannot be used on interfaces. This attribute is designed for functions, methods, and class constants, but PHP does not support marking entire interfaces as deprecated using this native attribute. Attempting to use it on an interface is invalid and will not have the intended effect.

## How to fix it

Remove the `#[\Deprecated]` attribute from the interface. To mark an interface as deprecated, use the `@deprecated` PHPDoc tag instead:

```diff-php
 <?php declare(strict_types = 1);

-#[\Deprecated]
+/** @deprecated Use NewLoggerInterface instead */
 interface LoggerInterface
 {
 	public function log(string $message): void;
 }
```
