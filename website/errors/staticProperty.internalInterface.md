---
title: "staticProperty.internalInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

// Defined in a third-party package:
// namespace ThirdParty;
// /** @internal */
// interface Config {
//     public static string $defaultValue;
// }

use ThirdParty\Config;

$value = Config::$defaultValue;
```

## Why is it reported?

A static property is being accessed on an interface that is marked as `@internal`. Internal interfaces are not part of the public API of the package that defines them. They may change or be removed in any version without notice.

This error can occur in two scenarios:
- The interface itself is internal, and a static property is accessed on it.
- The static property is accessed on a class, but the declaring class of the property is an internal interface.

## How to fix it

Use the public API of the package instead of accessing static properties on internal interfaces:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use ThirdParty\Config;
+use ThirdParty\PublicConfig;

-$value = Config::$defaultValue;
+$value = PublicConfig::$defaultValue;
```

If the interface is internal to your own project, the error will not be reported when accessing it from within the same root namespace.
