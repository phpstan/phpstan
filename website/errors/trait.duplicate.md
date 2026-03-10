---
title: "trait.duplicate"
shortDescription: "Same trait name is declared in multiple files."
ignorable: true
---

## Code example

This error is reported when the same trait name is declared in multiple files within the analysed codebase:

```php
// file1.php
<?php declare(strict_types = 1);

namespace App\Helpers;

trait MyTrait
{
	public function doSomething(): void
	{
	}
}
```

```php
// file2.php
<?php declare(strict_types = 1);

namespace App\Helpers;

trait MyTrait
{
	public function doSomethingElse(): void
	{
	}
}
```

## Why is it reported?

The same trait name is declared in multiple files within the registered [stub files](/user-guide/stub-files).

## How to fix it

Rename one of the duplicate traits to give it a unique fully qualified name:

```diff-php
 <?php declare(strict_types = 1);

 // file2.php
-namespace App\Helpers;
+namespace App\Legacy;

-trait MyTrait
+trait LegacyTrait
 {
 	public function doSomethingElse(): void
 	{
 	}
 }
```

Or remove the duplicate declaration if it was unintentional.
