---
title: "class.implementsDeprecatedClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewInterface instead */
interface OldInterface
{

}

class Foo implements OldInterface // reported
{

}
```

## Why is it reported?

The class in the `implements` clause is marked as `@deprecated`. Deprecated symbols are scheduled for removal in a future version, and new code should not rely on them. Continuing to implement a deprecated interface means the code will need to be updated when the deprecated symbol is removed.

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) package.

## How to fix it

Replace the deprecated interface with its suggested replacement, if one is provided in the deprecation message.

```diff-php
-class Foo implements OldInterface
+class Foo implements NewInterface
 {

 }
```
