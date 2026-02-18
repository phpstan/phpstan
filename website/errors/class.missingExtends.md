---
title: "class.missingExtends"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

abstract class BaseController
{
}

/**
 * @phpstan-require-extends BaseController
 */
interface ControllerInterface
{
}

class MyService implements ControllerInterface
{
}
```

## Why is it reported?

An interface or trait declares a `@phpstan-require-extends` tag that requires any implementing class (or class using the trait) to extend a specific base class. The class does not extend the required class.

In the example above, `ControllerInterface` requires implementing classes to extend `BaseController`, but `MyService` does not extend `BaseController`.

## How to fix it

Extend the required base class:

```diff-php
-class MyService implements ControllerInterface
+class MyService extends BaseController implements ControllerInterface
 {
 }
```
