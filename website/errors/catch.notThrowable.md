---
title: "catch.notThrowable"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class NotAnException
{
}

try {
	// ...
} catch (NotAnException $e) {
	// ...
}
```

## Why is it reported?

PHP only allows catching classes that implement the `Throwable` interface. All exception classes must either extend `\Exception` (which implements `Throwable`) or directly implement `Throwable`. Attempting to catch a class that does not implement `Throwable` will result in a fatal error.

In the example above, `NotAnException` is a regular class that does not extend `\Exception` or implement `\Throwable`, so it cannot be used in a `catch` block.

## How to fix it

Make the class extend `\Exception` or one of its subclasses:

```diff-php
 <?php declare(strict_types = 1);

-class NotAnException
+class NotAnException extends \RuntimeException
 {
 }

 try {
 	// ...
 } catch (NotAnException $e) {
 	// ...
 }
```

Alternatively, if this class should not be an exception, catch the correct exception class instead:

```diff-php
 <?php declare(strict_types = 1);

 try {
 	// ...
-} catch (NotAnException $e) {
+} catch (\RuntimeException $e) {
 	// ...
 }
```
