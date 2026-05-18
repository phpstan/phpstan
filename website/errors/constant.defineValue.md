---
title: "constant.defineValue"
shortDescription: "Value passed to define() does not match the type configured in dynamicConstantNames."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// phpstan.neon:
// parameters:
//   dynamicConstantNames:
//     DATABASE_ENGINE: string|null

define('DATABASE_ENGINE', false);
```

## Why is it reported?

When a global constant is listed in the [`dynamicConstantNames`](/config-reference#constants) configuration with an explicit type, PHPStan checks that values assigned via `define()` are compatible with that type. In the example above, `DATABASE_ENGINE` is configured to accept `string|null`, but `false` is being assigned.

This prevents the configured type from becoming inaccurate, which would lead to incorrect analysis results elsewhere in the codebase.

## How to fix it

Change the value to match the configured type:

```diff-php
-define('DATABASE_ENGINE', false);
+define('DATABASE_ENGINE', null);
```

Or update the configured type in `phpstan.neon` to accept the value:

```diff-php
 parameters:
 	dynamicConstantNames:
-		DATABASE_ENGINE: string|null
+		DATABASE_ENGINE: string|false|null
```
