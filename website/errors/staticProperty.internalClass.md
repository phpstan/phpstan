---
title: "staticProperty.internalClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

use Internal\Config;

$debug = Config::$debugMode;
```

## Why is it reported?

A static property is being accessed on a class that is marked as `@internal`. Internal classes are not meant to be used outside of their own package. Depending on their static properties creates a coupling to implementation details that can change at any time without following semantic versioning.

## How to fix it

Use the public API provided by the package instead of accessing internal class properties directly.

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use Internal\Config;
+use Public\Settings;

-$debug = Config::$debugMode;
+$debug = Settings::isDebug();
```
