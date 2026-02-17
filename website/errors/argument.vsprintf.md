---
title: "argument.vsprintf"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$result = vsprintf('%s is %d years old and lives in %s', ['Alice', 30]);
```

## Why is it reported?

The number of format placeholders in the `vsprintf` format string does not match the number of values in the array argument. The `vsprintf` function requires the array to contain exactly as many values as there are placeholders in the format string. A mismatch will result in missing values or ignored extra values at runtime.

In the example above, the format string `'%s is %d years old and lives in %s'` contains 3 placeholders, but the array only provides 2 values.

## How to fix it

Pass the correct number of values in the array to match the placeholders:

```diff-php
 <?php declare(strict_types = 1);

-$result = vsprintf('%s is %d years old and lives in %s', ['Alice', 30]);
+$result = vsprintf('%s is %d years old and lives in %s', ['Alice', 30, 'London']);
```

Or adjust the format string if fewer values are intended:

```diff-php
 <?php declare(strict_types = 1);

-$result = vsprintf('%s is %d years old and lives in %s', ['Alice', 30]);
+$result = vsprintf('%s is %d years old', ['Alice', 30]);
```
