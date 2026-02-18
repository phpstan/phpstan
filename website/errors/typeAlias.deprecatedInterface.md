---
title: "typeAlias.deprecatedInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewHandler instead */
interface OldHandler
{
}

/**
 * @phpstan-type HandlerType OldHandler
 */
class Factory
{
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

A type alias defined with `@phpstan-type` references a deprecated interface. Deprecated interfaces are scheduled for removal or replacement. Using them in type aliases propagates the dependency on a deprecated API to all code that uses the alias.

## How to fix it

Update the type alias to reference the replacement interface:

```diff-php
 /**
- * @phpstan-type HandlerType OldHandler
+ * @phpstan-type HandlerType NewHandler
  */
 class Factory
 {
 }
```

If the calling code is itself deprecated, the error will not be reported. Mark the class as deprecated if it is part of a deprecation migration:

```diff-php
+/** @deprecated */
 /**
  * @phpstan-type HandlerType OldHandler
  */
 class Factory
 {
 }
```
