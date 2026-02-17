---
title: "class.implementsDeprecatedTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewInterface instead */
trait OldTrait
{
}

class Foo implements OldTrait
{
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

A class has a deprecated trait in its `implements` clause. The trait is marked as `@deprecated` and is planned for removal.

In the example above, class `Foo` references `OldTrait` in its `implements` clause, and `OldTrait` is deprecated. Note that using a trait in `implements` is also invalid PHP -- only interfaces can be implemented. Traits should be included with `use` instead.

## How to fix it

Replace the deprecated trait with a proper interface:

```diff-php
 <?php declare(strict_types = 1);

-class Foo implements OldTrait
+class Foo implements NewInterface
 {
 }
```
