---
title: "interface.duplicate"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In file: src/Contracts/Logger.php
namespace App\Contracts;

interface Logger
{
	public function log(string $message): void;
}
```

```php
<?php declare(strict_types = 1);

// In file: src/Legacy/Logger.php
namespace App\Contracts;

interface Logger
{
	public function log(string $message): void;
}
```

## Why is it reported?

The same interface is declared in multiple files within the analysed codebase. PHP does not allow two interfaces with the same fully-qualified name. When the autoloader loads one of them, the other becomes unreachable, and if both files are included, a fatal error occurs.

In the example above, `App\Contracts\Logger` is defined in two different files.

## How to fix it

Remove the duplicate declaration and keep only one definition of the interface:

```diff-php
-// Delete or rename the duplicate file src/Legacy/Logger.php
```

If both interfaces are needed, give them different names or place them in different namespaces:

```diff-php
 <?php declare(strict_types = 1);

-namespace App\Contracts;
+namespace App\Legacy;

-interface Logger
+interface LegacyLogger
 {
 	public function log(string $message): void;
 }
```
