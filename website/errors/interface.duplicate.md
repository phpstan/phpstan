---
title: "interface.duplicate"
shortDescription: "Same interface is declared in multiple files."
ignorable: true
---

## Code example

This error is reported when the same interface is declared in multiple files within the analysed codebase:

```php
// file1.php
<?php declare(strict_types = 1);

namespace App\Contracts;

interface Logger
{
	public function log(string $message): void;
}
```

```php
// file2.php
<?php declare(strict_types = 1);

namespace App\Contracts;

interface Logger
{
	public function log(string $message): void;
}
```

## Why is it reported?

The same interface is declared in multiple files within the registered [stub files](/user-guide/stub-files).

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
