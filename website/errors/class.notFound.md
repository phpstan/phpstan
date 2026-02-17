---
title: "class.notFound"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo extends NonexistentClass
{
}
```

## Why is it reported?

The code references a class that PHPStan cannot find. This can happen when extending, implementing, instantiating, or type-hinting a class that does not exist. Common causes include:

- A typo in the class name
- A missing `use` import statement
- The class file is not included in the autoloader
- A missing Composer dependency

At runtime, referencing a non-existent class will cause a fatal error.

## How to fix it

Make sure the class exists and is properly autoloaded:

```diff-php
 <?php declare(strict_types = 1);

+use App\Models\BaseClass;
+
-class Foo extends NonexistentClass
+class Foo extends BaseClass
 {
 }
```

If the class comes from an external package, make sure the package is installed:

```bash
composer require vendor/package
```

If PHPStan cannot find a class that does exist at runtime, configure the autoloader or [`scanFiles`/`scanDirectories`](/user-guide/discovering-symbols#third-party-code-outside-of-composer-dependencies) in your PHPStan configuration.

Learn more: [Discovering Symbols](/user-guide/discovering-symbols)
