---
title: "staticMethod.inVoidCast"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Logger
{
	public static function log(string $message): bool
	{
		return file_put_contents('log.txt', $message) !== false;
	}
}

(void) Logger::log('Hello');
```

## Why is it reported?

The static method call is wrapped in a `(void)` cast, but the method does not require its return value to be used. The `(void)` cast is meant for explicitly discarding the return value of methods marked with `#[\NoDiscard]` to suppress the `staticMethod.resultDiscarded` error. When a method already allows its return value to be discarded, using `(void)` is unnecessary.

## How to fix it

Remove the `(void)` cast and call the method directly:

```diff-php
 <?php declare(strict_types = 1);

-(void) Logger::log('Hello');
+Logger::log('Hello');
```

If the return value is needed, assign it to a variable:

```diff-php
 <?php declare(strict_types = 1);

-(void) Logger::log('Hello');
+$success = Logger::log('Hello');
```
