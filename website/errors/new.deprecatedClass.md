---
title: "new.deprecatedClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewLogger instead */
class OldLogger
{
}

new OldLogger();
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

A deprecated class is being instantiated with `new`. The class has been marked with `@deprecated`, indicating it should no longer be used and may be removed in a future version.

## How to fix it

Replace the deprecated class with the recommended replacement:

```diff-php
-new OldLogger();
+new NewLogger();
```

If the calling code is itself deprecated, the error will not be reported. Mark the calling class or function as deprecated if it is part of a deprecation migration:

```diff-php
+/** @deprecated */
 function createLogger(): object
 {
 	return new OldLogger();
 }
```
