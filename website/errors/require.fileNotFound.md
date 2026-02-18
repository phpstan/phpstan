---
title: "require.fileNotFound"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

require 'a-file-that-does-not-exist.php';
```

## Why is it reported?

The path passed to `require()` resolves to a file that does not exist on disk. At runtime, `require` will produce a fatal error when the file cannot be found. PHPStan checks the path against the current working directory, the PHP include path, and the directory of the analysed file.

## How to fix it

Correct the file path:

```diff-php
-require 'a-file-that-does-not-exist.php';
+require __DIR__ . '/existing-file.php';
```

Or ensure the file exists at the expected location. When paths are computed dynamically, PHPStan can only check constant string paths.
