---
title: "parameter.deprecatedClass"
ignorable: true
---

This error is reported by `phpstan/phpstan-deprecation-rules`.

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewLogger instead */
class OldLogger
{
}

function process(OldLogger $logger): void
{
}
```

## Why is it reported?

A function or method parameter uses a deprecated class as its type declaration. The class has been marked with a `@deprecated` PHPDoc tag, indicating it should no longer be used. Using a deprecated class in a parameter type ties new code to an obsolete API.

## How to fix it

Replace the deprecated class with its recommended replacement in the parameter type declaration.

```diff-php
 <?php declare(strict_types = 1);

-function process(OldLogger $logger): void
+function process(NewLogger $logger): void
 {
 }
```
