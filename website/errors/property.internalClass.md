---
title: "property.internalClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In package vendor/some-library:

namespace SomeLibrary;

/** @internal */
class InternalConfig
{
	public string $value = 'default';
}
```

```php
<?php declare(strict_types = 1);

// In your code:

namespace App;

use SomeLibrary\InternalConfig;

function readConfig(InternalConfig $config): string
{
	return $config->value; // ERROR: Access to property $value of internal class SomeLibrary\InternalConfig.
}
```

## Why is it reported?

The code accesses a property on a class that is marked as `@internal`. Internal classes are not part of the package's public API and may change or be removed without notice in future versions. Accessing properties on internal classes creates a fragile dependency on implementation details.

## How to fix it

Use the public API provided by the library instead of accessing properties on internal classes:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use SomeLibrary\InternalConfig;
+use SomeLibrary\Config;

-function readConfig(InternalConfig $config): string
+function readConfig(Config $config): string
 {
-	return $config->value;
+	return $config->getValue();
 }
```

If no public alternative exists, consider reaching out to the package maintainers to request a public API for your use case.
