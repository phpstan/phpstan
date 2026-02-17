---
title: "class.extendsDeprecatedInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewInterface instead */
interface OldInterface
{
}

class Foo extends OldInterface
{
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

A class extends a type that has been marked as `@deprecated`. Extending deprecated types ties your class to implementations that are planned for removal.

In the example above, class `Foo` extends `OldInterface`, which is deprecated.

## How to fix it

Replace the deprecated type with its recommended replacement:

```diff-php
 <?php declare(strict_types = 1);

-class Foo extends OldInterface
+class Foo extends NewInterface
 {
 }
```
