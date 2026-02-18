---
title: "new.deprecatedTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewLogger instead */
trait OldLogger
{
	public function log(string $message): void {}
}

class Logger
{
	use OldLogger;
}

$logger = new Logger();
```

## Why is it reported?

The `new` expression instantiates a class that uses a deprecated trait. The trait has been marked with a `@deprecated` PHPDoc tag, indicating it is scheduled for removal or replacement.

This rule is provided by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) package.

## How to fix it

Replace the deprecated trait with its recommended replacement:

```diff-php
 class Logger
 {
-	use OldLogger;
+	use NewLogger;
 }
```

Or if the entire class is deprecated, instantiate the replacement class:

```diff-php
-$logger = new Logger();
+$logger = new NewLogger();
```
