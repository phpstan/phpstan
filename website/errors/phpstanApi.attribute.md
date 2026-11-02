---
title: "phpstanApi.attribute"
shortDescription: "Using a PHPStan attribute that is not supported in third-party code."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

use PHPStan\DependencyInjection\ContainerExtension;

#[ContainerExtension(name: 'foo')]
class Extension extends \Nette\DI\CompilerExtension
{

}
```

## Why is it reported?

The code uses an attribute class from PHPStan that is meant only for PHPStan's own internal use. Such attributes are not processed when used in third-party code like extensions, so they simply will not work as expected — for example the class will not be registered or configured the way the attribute suggests.

Only attribute classes marked with the `@api` PHPDoc tag (like `#[RegisteredRule]`, `#[AutowiredService]` or `#[AutowiredParameter]`) are supported outside of PHPStan itself.

<!-- No "How to fix it" section: these attributes are simply non-translatable to a working alternative. -->
