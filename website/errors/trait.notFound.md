---
title: "trait.notFound"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

class Foo
{
	use NonexistentTrait;
}
```

## Why is it reported?

The `use` statement inside a class body references a trait that PHPStan cannot find. This can happen when:

- The trait name contains a typo
- A `use` import statement is missing
- The trait file is not included in the autoloader
- A Composer dependency is missing

At runtime, referencing a non-existent trait will cause a fatal error.

## How to fix it

Make sure the trait exists and is properly autoloaded:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

+use App\Traits\ExistingTrait;
+
 class Foo
 {
-	use NonexistentTrait;
+	use ExistingTrait;
 }
```

If the trait comes from an external package, make sure the package is installed:

```bash
composer require vendor/package
```

If PHPStan cannot find a trait that does exist at runtime, configure the autoloader or [`scanFiles`/`scanDirectories`](/user-guide/discovering-symbols#third-party-code-outside-of-composer-dependencies) in your PHPStan configuration.

Learn more: [Discovering Symbols](/user-guide/discovering-symbols)
