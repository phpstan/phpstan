---
title: "new.deprecatedTrait"
shortDescription: "Instantiation of a deprecated trait."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewHelper instead */
trait OldTrait
{
}

$x = new OldTrait();
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

A deprecated trait is used in a `new` expression. The trait has been marked with `@deprecated`, indicating it is scheduled for removal or replacement.

## How to fix it

Use the replacement type suggested in the deprecation message:

```diff-php
-$x = new OldTrait();
+$x = new NewHelper();
```
