---
title: "class.implementsDeprecatedEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewInterface instead */
enum OldEnum
{
	case Foo;
}

class Bar implements OldEnum
{
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

A class has a deprecated enum in its `implements` clause. The enum is marked as `@deprecated` and is planned for removal.

In the example above, class `Bar` references `OldEnum` in its `implements` clause, and `OldEnum` is deprecated. Note that using an enum in `implements` is also invalid PHP -- only interfaces can be implemented.

## How to fix it

Replace the deprecated enum with a proper interface:

```diff-php
 <?php declare(strict_types = 1);

-class Bar implements OldEnum
+class Bar implements NewInterface
 {
 }
```
