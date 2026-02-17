---
title: "method.deprecatedInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewLogger instead */
interface OldLogger
{
	public function log(string $message): void;
}

function doFoo(OldLogger $logger): void
{
	$logger->log('hello'); // ERROR: Call to method log() of deprecated interface OldLogger.
}
```

## Why is it reported?

This error is reported by `phpstan/phpstan-deprecation-rules`.

A method is being called on an instance typed as an interface that has been marked as `@deprecated`. Even though the method itself is not deprecated, the entire interface is deprecated, which means all usage of the interface -- including calling its methods -- should be replaced with the suggested alternative.

## How to fix it

Replace the usage of the deprecated interface with its recommended replacement:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(OldLogger $logger): void
+function doFoo(NewLogger $logger): void
 {
 	$logger->log('hello');
 }
```
