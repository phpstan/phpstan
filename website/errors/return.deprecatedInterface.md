---
title: "return.deprecatedInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewInterface instead */
interface OldInterface
{
}

function createHandler(): OldInterface
{
    // ...
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

The native return type declaration of a function or method references an interface that is marked as `@deprecated`. Using a deprecated interface in a return type creates a dependency on a symbol that is scheduled for removal or replacement. Callers of this function will also depend on the deprecated interface.

## How to fix it

Replace the deprecated interface in the return type with its recommended replacement:

```diff-php
 <?php declare(strict_types = 1);

-function createHandler(): OldInterface
+function createHandler(): NewInterface
 {
     // ...
 }
```

If the function is itself deprecated, the error will not be reported. Mark the function as deprecated to suppress the error if the function is part of a deprecation migration:

```diff-php
 <?php declare(strict_types = 1);

+/** @deprecated Use createNewHandler() instead */
 function createHandler(): OldInterface
 {
     // ...
 }
```
