---
title: "function.notFound"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

echo undefinedFunction();
```

## Why is it reported?

The code calls a function that PHPStan cannot find. This usually means the function does not exist, is misspelled, or is defined in a file that PHPStan does not scan (such as a conditionally loaded file or an extension stub).

## How to fix it

If the function name is a typo, correct it:

```diff-php
 <?php declare(strict_types = 1);

-echo aray_merge([1], [2]);
+echo array_merge([1], [2]);
```

If the function is defined elsewhere, make sure PHPStan can discover it by including the file in `scanFiles` or `scanDirectories`:

```neon
parameters:
    scanFiles:
        - functions.php
```

If the function comes from a PHP extension, make sure PHPStan knows about it by adding the appropriate extension stubs.
