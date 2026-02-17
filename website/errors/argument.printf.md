---
title: "argument.printf"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$name = 'world';
echo sprintf('%s is %d years old', $name);
```

## Why is it reported?

The number of format placeholders in the `printf`/`sprintf` format string does not match the number of values passed. PHP's `sprintf` and `printf` functions require exactly as many value arguments as there are placeholders in the format string. A mismatch will result in missing values or ignored extra arguments at runtime.

In the example above, the format string `'%s is %d years old'` contains 2 placeholders (`%s` and `%d`), but only 1 value (`$name`) is provided.

## How to fix it

Pass the correct number of arguments to match the placeholders:

```diff-php
 <?php declare(strict_types = 1);

 $name = 'world';
-echo sprintf('%s is %d years old', $name);
+echo sprintf('%s is %d years old', $name, 25);
```

Or adjust the format string if fewer values are intended:

```diff-php
 <?php declare(strict_types = 1);

 $name = 'world';
-echo sprintf('%s is %d years old', $name);
+echo sprintf('%s says hello', $name);
```
