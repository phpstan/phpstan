---
title: "constant.notFound"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

echo UNDEFINED_CONSTANT;
```

## Why is it reported?

The code references a constant that PHPStan cannot find. This usually means the constant does not exist, is defined in a file that PHPStan does not scan, or is provided by an extension that is not loaded.

## How to fix it

If the constant is defined elsewhere, make sure PHPStan can discover it by including the file in `scanFiles` or `scanDirectories`:

```neon
parameters:
    scanFiles:
        - constants.php
```

If the constant name is a typo, correct it:

```diff-php
 <?php declare(strict_types = 1);

-echo UNDEFNED_CONSTANT;
+echo UNDEFINED_CONSTANT;
```
