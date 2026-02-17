---
title: "assert.internalInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In package vendor/some-library:

namespace SomeLibrary;

/** @internal */
interface Logger
{
	public function log(string $message): void;
}
```

```php
<?php declare(strict_types = 1);

// In your code:

namespace App;

use SomeLibrary\Logger;

class Checker
{
	/**
	 * @phpstan-assert Logger $value
	 */
	public function assertLogger(mixed $value): void
	{
		// ...
	}
}
```

## Why is it reported?

A `@phpstan-assert` PHPDoc tag references an interface that is marked as `@internal`. Internal types are not meant to be used outside of the package or namespace where they are defined. Depending on internal types in your assertions creates a fragile dependency on implementation details that can change without notice.

In the example above, the `@phpstan-assert Logger $value` tag references the internal `Logger` interface from `SomeLibrary`.

## How to fix it

Use a public (non-internal) type in the `@phpstan-assert` tag instead. If you need to assert against similar functionality, check whether the library provides a public interface, or define your own type:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use SomeLibrary\Logger;
+use Psr\Log\LoggerInterface;

 class Checker
 {
 	/**
-	 * @phpstan-assert Logger $value
+	 * @phpstan-assert LoggerInterface $value
 	 */
-	public function assertLogger(mixed $value): void
+	public function assertLoggerInterface(mixed $value): void
 	{
 		// ...
 	}
 }
```
