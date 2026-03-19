---
title: "phpstanPlayground.arrayKeyCast"
shortDescription: "Literal array key will be silently cast to a different type."
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

$a = [
	'1' => 'one',   // key '1' (string) will be cast to 1 (int)
	null => 'empty', // key null will be cast to '' (string)
	2.5 => 'two',    // key 2.5 (float) will be cast to 2 (int)
	true => 'yes',   // key true (bool) will be cast to 1 (int)
	false => 'no',   // key false (bool) will be cast to 0 (int)
];
```

## Why is it reported?

PHP silently casts array keys to `int` or `string` when constructing arrays. A numeric string like `'1'` becomes the integer `1`, `null` becomes the empty string `''`, floats are truncated to integers, and booleans become `0` or `1`. This means the array may not have the keys the developer intended, and values can silently overwrite each other.

This rule is only active on the [PHPStan playground](https://phpstan.org/try).

Learn more: [Why Array String Keys Are Not Type-Safe in PHP](/blog/why-array-string-keys-are-not-type-safe)

## How to fix it

Use array keys of the type that PHP will actually store:

```diff-php
 $a = [
-	'1' => 'one',
+	1 => 'one',
 ];
```

For `null` keys, use the empty string explicitly:

```diff-php
 $a = [
-	null => 'empty',
+	'' => 'empty',
 ];
```

For float keys, use the truncated integer:

```diff-php
 $a = [
-	2.5 => 'two',
+	2 => 'two',
 ];
```

For boolean keys, use the corresponding integer:

```diff-php
 $a = [
-	true => 'yes',
-	false => 'no',
+	1 => 'yes',
+	0 => 'no',
 ];
```
