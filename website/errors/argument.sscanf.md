---
title: "argument.sscanf"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

sscanf('42 hello', '%d%d', $number);
```

## Why is it reported?

The format string passed to `sscanf()` contains placeholders that expect a corresponding variable argument for each one. When `sscanf()` is called with additional arguments beyond the format string, each `%` placeholder must have a matching variable to store the scanned value. A mismatch between the number of placeholders and the number of provided variables means some values will not be captured.

## How to fix it

Pass the correct number of variable arguments to match the format string placeholders:

```diff-php
 <?php declare(strict_types = 1);

-sscanf('42 hello', '%d%d', $number);
+sscanf('42 hello', '%d%d', $number, $other);
```

Or adjust the format string to match the number of arguments:

```diff-php
 <?php declare(strict_types = 1);

-sscanf('42 hello', '%d%d', $number);
+sscanf('42 hello', '%d', $number);
```
