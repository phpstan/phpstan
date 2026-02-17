---
title: "method.inVoidCast"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Logger
{
	public function log(string $message): bool
	{
		return file_put_contents('log.txt', $message) !== false;
	}
}

$logger = new Logger();
(void) $logger->log('hello');
```

## Why is it reported?

The method call is wrapped in a `(void)` cast, but the method does not require its return value to be used. The `(void)` cast is intended for suppressing the "result discarded" error on methods marked with `#[\NoDiscard]` (or determined to require their return value to be used). When a method already allows discarding its return value, using `(void)` is unnecessary and misleading.

## How to fix it

Remove the `(void)` cast and call the method normally:

```diff-php
 <?php declare(strict_types = 1);

 $logger = new Logger();
-(void) $logger->log('hello');
+$logger->log('hello');
```

If the return value should be used, assign it to a variable:

```diff-php
 <?php declare(strict_types = 1);

 $logger = new Logger();
-(void) $logger->log('hello');
+$success = $logger->log('hello');
```
