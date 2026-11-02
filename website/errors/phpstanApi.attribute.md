---
title: "phpstanApi.attribute"
shortDescription: "Using a PHPStan attribute not covered by the backward compatibility promise."
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

The code uses an attribute class from PHPStan that is not covered by the [backward compatibility promise](/developing-extensions/backward-compatibility-promise). Only attribute classes marked with the `@api` PHPDoc tag (like `#[RegisteredRule]`, `#[AutowiredService]` or `#[AutowiredParameter]`) are part of the stable public API. Other attributes are internal to PHPStan — their name, parameters or behaviour might change in a minor PHPStan version, which could silently break an extension relying on them.

## How to fix it

Replace the internal attribute with a supported alternative. For example, instead of registering a DI container extension with `#[ContainerExtension]`, register it in the configuration file:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use PHPStan\DependencyInjection\ContainerExtension;
-
-#[ContainerExtension(name: 'foo')]
 class Extension extends \Nette\DI\CompilerExtension
 {

 }
```

```neon
extensions:
	foo: App\Extension
```

Attributes covered by the backward compatibility promise, such as `#[RegisteredRule]`, `#[RegisteredCollector]`, `#[AutowiredService]`, `#[AutowiredParameter]`, `#[NonAutowiredService]`, `#[GenerateFactory]` and `#[Conditional]`, can be used freely.

If you believe the attribute should be covered by the backward compatibility promise, open a discussion at [github.com/phpstan/phpstan/discussions](https://github.com/phpstan/phpstan/discussions).
