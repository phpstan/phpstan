---
title: "argument.sprintf"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$name = 'world';
$result = sprintf('%s is %d years old and lives in %s', $name, 25);
```

## Why is it reported?

The number of format placeholders in the `sprintf` format string does not match the number of values passed. The `sprintf` function requires exactly as many value arguments as there are placeholders in the format string. A mismatch will result in missing values or ignored extra arguments at runtime.

In the example above, the format string `'%s is %d years old and lives in %s'` contains 3 placeholders (`%s`, `%d`, `%s`), but only 2 values (`$name`, `25`) are provided.

## How to fix it

Pass the correct number of arguments to match the placeholders:

```diff-php
 <?php declare(strict_types = 1);

 $name = 'world';
-$result = sprintf('%s is %d years old and lives in %s', $name, 25);
+$result = sprintf('%s is %d years old and lives in %s', $name, 25, 'London');
```

Or adjust the format string if fewer values are intended:

```diff-php
 <?php declare(strict_types = 1);

 $name = 'world';
-$result = sprintf('%s is %d years old and lives in %s', $name, 25);
+$result = sprintf('%s is %d years old', $name, 25);
```
