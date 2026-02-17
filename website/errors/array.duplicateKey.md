---
title: "array.duplicateKey"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$array = [
	'foo' => 1,
	'bar' => 2,
	'foo' => 3,
];
```

## Why is it reported?

The array literal contains duplicate keys. When an array has duplicate keys, PHP silently overwrites the earlier value with the later one. This is usually a mistake -- either the key or the value is wrong. Only the last value for the duplicate key will be preserved.

In the example above, the key `'foo'` appears twice. The first value `1` will be silently overwritten by `3`.

## How to fix it

Use unique keys for each array entry:

```diff-php
 <?php declare(strict_types = 1);

 $array = [
 	'foo' => 1,
 	'bar' => 2,
-	'foo' => 3,
+	'baz' => 3,
 ];
```

Or remove the duplicate entry if it was unintentional:

```diff-php
 <?php declare(strict_types = 1);

 $array = [
 	'foo' => 1,
 	'bar' => 2,
-	'foo' => 3,
 ];
```
