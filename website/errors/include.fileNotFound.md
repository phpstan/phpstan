---
title: "include.fileNotFound"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

include 'non-existent-file.php';
```

## Why is it reported?

The file path passed to `include` does not point to an existing file. PHPStan checks whether the file exists in the current working directory, the PHP include path, and the directory of the analysed file.

## How to fix it

Fix the file path to point to an existing file:

```diff-php
 <?php declare(strict_types = 1);

-include 'non-existent-file.php';
+include 'existing-file.php';
```
