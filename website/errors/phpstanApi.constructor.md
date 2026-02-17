---
title: "phpstanApi.constructor"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use PHPStan\Type\StringType;

// Inside a PHPStan extension
$type = new StringType();
```

## Why is it reported?

This error is reported when code instantiates a PHPStan internal class whose constructor is not covered by the [backward compatibility promise](https://phpstan.org/developing-extensions/backward-compatibility-promise). PHPStan reserves the right to change the constructor signature of such classes in minor versions, which could break extensions that directly instantiate them.

Only classes with `@api` on their constructor are guaranteed to have a stable constructor signature across minor releases.

## How to fix it

Check whether the class has a factory method or service that is part of the public API. In many cases, PHPStan provides `@api`-annotated factory methods or the class should be obtained through the dependency injection container rather than instantiated directly.

If the class genuinely needs to be instantiated and the constructor is not marked as `@api`, open a discussion at [github.com/phpstan/phpstan/discussions](https://github.com/phpstan/phpstan/discussions) to request that it be covered by the backward compatibility promise.
