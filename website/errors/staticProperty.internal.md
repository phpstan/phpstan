---
title: "staticProperty.internal"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

// Defined in a third-party package:
// namespace ThirdParty;
// class Config {
//     /** @internal */
//     public static string $secretKey = 'abc';
// }

use ThirdParty\Config;

$key = Config::$secretKey;
```

## Why is it reported?

A static property that is marked as `@internal` is being accessed from outside the package that defines it. Internal properties are implementation details not meant to be part of the public API. They may change or be removed in any version without notice.

This error can occur when:
- The static property itself has the `@internal` tag.
- The declaring class is in a different root namespace than the accessing code.

## How to fix it

Use the public API of the package instead of accessing internal static properties:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use ThirdParty\Config;
+use ThirdParty\Settings;

-$key = Config::$secretKey;
+$key = Settings::getApiKey();
```

If the property is internal to your own project, the error will not be reported when accessing it from within the same root namespace.
