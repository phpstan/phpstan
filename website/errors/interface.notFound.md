---
title: "interface.notFound"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

interface MyInterface extends NonExistentInterface // ERROR: Interface MyInterface extends unknown interface NonExistentInterface.
{
}
```

## Why is it reported?

The interface declaration extends an interface that does not exist. PHP requires all extended interfaces to be valid and loadable. This typically happens when the interface name contains a typo, the namespace is incorrect, or the package providing the interface is not installed.

## How to fix it

Fix the interface name or namespace:

```diff-php
 <?php declare(strict_types = 1);

-interface MyInterface extends NonExistentInterface
+interface MyInterface extends ExistingInterface
 {
 }
```

If the interface comes from a third-party package, make sure the package is installed via Composer and that the correct namespace is used. If PHPStan is not aware of the interface, check the [discovering symbols](https://phpstan.org/user-guide/discovering-symbols) guide.
