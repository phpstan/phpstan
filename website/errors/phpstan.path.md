---
title: "phpstan.path"
ignorable: false
---

## Code example

This error does not correspond to a specific PHP code pattern. It is emitted when PHPStan is configured to analyse a path that does not exist or is a directory instead of a file.

## Why is it reported?

PHPStan was given a file path to analyse, but the path either does not exist on the filesystem or points to a directory instead of a file. PHPStan can only analyse PHP files, not directories or missing paths.

This error is not ignorable because PHPStan cannot analyse a file that does not exist.

## How to fix it

Check the `paths` configuration in `phpstan.neon` and make sure all listed paths point to existing files or directories:

```diff-php
 # phpstan.neon
 parameters:
     paths:
-        - src/OldModule
+        - src/NewModule
```

If files have been moved or renamed, update the configuration to reflect the current file structure.
