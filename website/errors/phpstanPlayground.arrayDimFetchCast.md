---
title: "phpstanPlayground.arrayDimFetchCast"
shortDescription: "Array access key will be silently cast to a different type."
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

$a = ['foo' => 1, 'bar' => 2];

echo $a['1'];  // key '1' (string) will be cast to 1 (int)
echo $a[null]; // key null will be cast to '' (string)
echo $a[2.5];  // key 2.5 (float) will be cast to 2 (int)
echo $a[true]; // key true (bool) will be cast to 1 (int)
```

## Why is it reported?

PHP silently casts array keys to `int` or `string` when accessing array elements. A numeric string like `'1'` is cast to the integer `1`, `null` becomes the empty string `''`, floats are truncated to integers, and booleans become `0` or `1`. This means the accessed key may not match what the developer intended, leading to unexpected values or missing entries.

This rule is only active on the [PHPStan playground](https://phpstan.org/try).

Learn more: [Why Array String Keys Are Not Type-Safe in PHP](/blog/why-array-string-keys-are-not-type-safe)

## How to fix it

Use array keys that match the expected type without implicit casting:

```diff-php
 $a = [1 => 'one', 2 => 'two'];

-echo $a['1'];
+echo $a[1];
```

For `null` keys, use the explicit string equivalent:

```diff-php
-echo $a[null];
+echo $a[''];
```

For float keys, use the truncated integer directly:

```diff-php
-echo $a[2.5];
+echo $a[2];
```

For boolean keys, use the corresponding integer:

```diff-php
-echo $a[true];
+echo $a[1];
```
