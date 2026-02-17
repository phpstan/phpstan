---
title: "offsetAccess.noDim"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$array = [1, 2, 3];
$value = $array[];
```

## Why is it reported?

The empty dimension syntax `$array[]` is being used for reading. In PHP, `$array[]` without a dimension index is only valid on the left side of an assignment (e.g., `$array[] = $value`), where it appends a new element. Using it for reading has no meaning and will produce a fatal error at runtime.

## How to fix it

Specify an explicit array index or key to access the desired element.

```diff-php
 <?php declare(strict_types = 1);

 $array = [1, 2, 3];
-$value = $array[];
+$value = $array[0];
```
