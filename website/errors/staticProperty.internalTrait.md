---
title: "staticProperty.internalTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

// Defined in a third-party package:
// namespace ThirdParty;
// /** @internal */
// trait InternalConfig {
//     public static string $debugMode = 'off';
// }

use ThirdParty\InternalConfig;

$mode = InternalConfig::$debugMode;
```

## Why is it reported?

A static property is being accessed on a trait that is marked as `@internal`. Internal traits are not part of the public API of the package that defines them. They may change or be removed in any version without notice. Accessing static properties on internal traits creates a dependency on implementation details that should not be relied upon.

## How to fix it

Use the public API of the package instead of accessing static properties on internal traits:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use ThirdParty\InternalConfig;
+use ThirdParty\PublicConfig;

-$mode = InternalConfig::$debugMode;
+$mode = PublicConfig::getDebugMode();
```

If the trait is internal to the same package, the error will not be reported. The `@internal` restriction only applies to cross-package usage.
