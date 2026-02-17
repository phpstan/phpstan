---
title: "trait.duplicate"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// file: src/Helpers/MyTrait.php
namespace App\Helpers;

trait MyTrait
{
	public function doSomething(): void
	{
	}
}
```

```php
<?php declare(strict_types = 1);

// file: src/Legacy/MyTrait.php
namespace App\Helpers;

trait MyTrait
{
	public function doSomethingElse(): void
	{
	}
}
```

## Why is it reported?

The same trait name is declared in multiple files. When PHP autoloading encounters two traits with the same fully qualified name, only one will be loaded. The other declaration is effectively dead code, which can lead to confusion and unexpected behaviour depending on which file gets loaded first.

## How to fix it

Rename one of the duplicate traits to give it a unique fully qualified name:

```diff-php
 <?php declare(strict_types = 1);

 // file: src/Legacy/MyTrait.php
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
