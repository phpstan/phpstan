---
title: "argument.fscanf"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @var resource $handle */
$handle = fopen('data.txt', 'r');

fscanf($handle, '%d%d', $number);
```

## Why is it reported?

The format string passed to `fscanf()` contains placeholders that expect a corresponding variable argument for each one. When `fscanf()` is called with additional arguments beyond the format string, each `%` placeholder must have a matching variable to store the scanned value. A mismatch between the number of placeholders and the number of provided variables means some values will not be captured.

## How to fix it

Pass the correct number of variable arguments to match the format string placeholders:

```diff-php
 <?php declare(strict_types = 1);

 /** @var resource $handle */
 $handle = fopen('data.txt', 'r');

-fscanf($handle, '%d%d', $number);
+fscanf($handle, '%d%d', $number, $other);
```

Or adjust the format string to match the number of arguments:

```diff-php
 <?php declare(strict_types = 1);

 /** @var resource $handle */
 $handle = fopen('data.txt', 'r');

-fscanf($handle, '%d%d', $number);
+fscanf($handle, '%d', $number);
```
