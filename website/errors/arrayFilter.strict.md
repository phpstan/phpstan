---
title: "arrayFilter.strict"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @var list<int> $list */
$list = [1, 2, 3];

// No callback provided - uses loose comparison
array_filter($list);
```

## Why is it reported?

When `array_filter()` is called without a callback (parameter #2), PHP uses loose comparison to determine which elements to keep. Each value is cast to `bool` using PHP's type juggling rules, which means values like `0`, `''`, `'0'`, `null`, and `[]` are considered falsy and are removed. This implicit behaviour can lead to unexpected results when the intent is to filter specific values.

This rule is part of [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules) and requires an explicit callback to make the filtering logic clear and intentional.

## How to fix it

Provide an explicit callback function that defines the filtering logic:

```diff-php
 <?php declare(strict_types = 1);

 /** @var list<int> $list */
 $list = [1, 2, 3];

-array_filter($list);
+array_filter($list, static function (int $value): bool {
+    return $value > 0;
+});
```

If the intent is truly to remove all falsy values, make it explicit:

```diff-php
 <?php declare(strict_types = 1);

 /** @var list<int> $list */
 $list = [1, 2, 3];

-array_filter($list);
+array_filter($list, static function (int $value): bool {
+    return (bool) $value;
+});
```
