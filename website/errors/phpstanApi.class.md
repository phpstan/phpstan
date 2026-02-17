---
title: "phpstanApi.class"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use PHPStan\Type\FileTypeMapper;

class MyFileTypeMapper extends FileTypeMapper
{
}
```

## Why is it reported?

The code extends a PHPStan class or uses `instanceof` against a PHPStan class that is not covered by the [backward compatibility promise](https://phpstan.org/developing-extensions/backward-compatibility-promise). The class might change in a minor PHPStan version, which could break your code.

PHPStan marks certain classes with `@api` to indicate they are safe to extend. Classes without this tag are considered internal and may have their implementation changed in any minor release.

This error is also reported when performing an `instanceof` check against a PHPStan class that is not covered by the backward compatibility promise.

## How to fix it

Use only classes that are covered by the backward compatibility promise (marked with `@api`). Check the PHPStan source code to see if the class you want to extend is part of the public API.

```diff-php
 <?php declare(strict_types = 1);

-use PHPStan\Type\FileTypeMapper;
-
-class MyFileTypeMapper extends FileTypeMapper
-{
-}
+// Use PHPStan's public API instead of extending internal classes
```

If you believe the class should be covered by the backward compatibility promise, open a discussion at [github.com/phpstan/phpstan/discussions](https://github.com/phpstan/phpstan/discussions).

Learn more: [Backward Compatibility Promise](https://phpstan.org/developing-extensions/backward-compatibility-promise)
