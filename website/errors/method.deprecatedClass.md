---
title: "method.deprecatedClass"
ignorable: true
---

This error is reported by `phpstan/phpstan-deprecation-rules`.

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewLogger instead */
class OldLogger
{
	public function log(string $message): void
	{
	}
}

$logger = new OldLogger();
$logger->log('hello');
```

## Why is it reported?

A method is being called on an instance of a class that has been marked as `@deprecated`. Even though the method itself is not deprecated, the entire class is deprecated, which means all usage of the class -- including calling its methods -- should be replaced with the suggested alternative.

## How to fix it

Replace the usage of the deprecated class with its recommended replacement.

```diff-php
 <?php declare(strict_types = 1);

-$logger = new OldLogger();
+$logger = new NewLogger();
 $logger->log('hello');
```
