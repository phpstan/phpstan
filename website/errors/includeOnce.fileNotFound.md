---
title: "includeOnce.fileNotFound"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

include_once 'a-file-that-does-not-exist.php';
```

## Why is it reported?

The path passed to `include_once()` does not point to an existing file. Unlike `require_once`, `include_once` will emit a warning rather than a fatal error at runtime when the file cannot be found, but the included code will not be available and may cause subsequent errors.

PHPStan checks that the path resolves to an existing file by looking in the current working directory, the include path, and the directory of the script being analysed.

## How to fix it

Verify that the file path is correct and the file exists at the expected location:

```diff-php
 <?php declare(strict_types = 1);

-include_once 'a-file-that-does-not-exist.php';
+include_once __DIR__ . '/helpers.php';
```

If the file is resolved dynamically and PHPStan cannot determine the path statically, consider using a constant string expression that PHPStan can evaluate, such as `__DIR__` combined with a relative path.
