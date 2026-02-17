---
title: "argument.vprintf"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

vprintf('%s is %d years old', ['Alice']);
```

## Why is it reported?

The number of format placeholders in the `vprintf` format string does not match the number of values in the array argument. The `vprintf` function requires the array to contain exactly as many values as there are placeholders in the format string. A mismatch will result in missing values or ignored extra values at runtime.

In the example above, the format string `'%s is %d years old'` contains 2 placeholders (`%s` and `%d`), but the array only provides 1 value (`'Alice'`).

## How to fix it

Pass the correct number of values in the array to match the placeholders:

```diff-php
 <?php declare(strict_types = 1);

-vprintf('%s is %d years old', ['Alice']);
+vprintf('%s is %d years old', ['Alice', 30]);
```

Or adjust the format string if fewer values are intended:

```diff-php
 <?php declare(strict_types = 1);

-vprintf('%s is %d years old', ['Alice']);
+vprintf('%s says hello', ['Alice']);
```
