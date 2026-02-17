---
title: "requireOnce.fileNotFound"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

require_once 'a-file-that-does-not-exist.php';
```

## Why is it reported?

The path passed to `require_once()` does not point to an existing file. PHP will produce a fatal error at runtime when it cannot find the file specified in a `require_once` statement.

PHPStan checks that the path resolves to an existing file by looking in the current working directory, the include path, and the directory of the script being analysed.

## How to fix it

Verify that the file path is correct and the file exists at the expected location:

```diff-php
 <?php declare(strict_types = 1);

-require_once 'a-file-that-does-not-exist.php';
+require_once __DIR__ . '/bootstrap.php';
```

If the file is resolved dynamically and PHPStan cannot determine the path statically, consider using a constant string expression that PHPStan can evaluate, such as `__DIR__` combined with a relative path.
