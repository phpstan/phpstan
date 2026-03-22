---
title: "argument.parameterRenamedInSubtype"
shortDescription: "Using a named argument whose parameter name was renamed in a subtype, causing a runtime error."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

interface Logger
{
	public function log(string $message): void;
}

class FileLogger implements Logger
{
	public function log(string $text): void
	{
	}
}

function doLog(Logger $logger): void
{
	$logger->log(message: 'Hello');
}
```

## Why is it reported?

The call uses a named argument `message:` based on the parameter name defined in `Logger::log()`, but `FileLogger` renames that parameter to `$text`. If the actual object at runtime is a `FileLogger`, PHP will throw an `Error` because it does not recognize the named argument `message`.

Named arguments in PHP 8.0+ are matched by name, not position. When a subclass or implementation renames a parameter, calls using the parent's parameter name will fail at runtime for instances of the subclass.

## How to fix it

Rename the parameter in the subtype to match the parent definition:

```diff-php
 class FileLogger implements Logger
 {
-	public function log(string $text): void
+	public function log(string $message): void
 	{
 	}
 }
```

Alternatively, use positional arguments instead of named arguments:

```diff-php
 function doLog(Logger $logger): void
 {
-	$logger->log(message: 'Hello');
+	$logger->log('Hello');
 }
```
