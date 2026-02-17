---
title: "interface.extendsDeprecatedClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewInterface instead */
interface OldInterface
{
}

interface MyInterface extends OldInterface
{
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

An interface extends another type that has been marked as `@deprecated`. Deprecated types are planned for removal in a future version, and code should not depend on them.

## How to fix it

Replace the deprecated type with its recommended replacement:

```diff-php
 <?php declare(strict_types = 1);

-interface MyInterface extends OldInterface
+interface MyInterface extends NewInterface
 {
 }
```
