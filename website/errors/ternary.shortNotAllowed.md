---
title: "ternary.shortNotAllowed"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$value = $input ?: 'default';
```

## Why is it reported?

This error is reported by [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules).

The short ternary operator (`?:`) is being used. The short ternary returns the left operand if it is truthy, or the right operand otherwise. This is problematic because it relies on PHP's loose truthiness rules, which can lead to unexpected behavior. For example, `0 ?: 'default'` returns `'default'` even though `0` is a valid value.

## How to fix it

If checking for `null`, use the null coalescing operator (`??`) which only checks for `null` without evaluating truthiness:

```diff-php
-$value = $input ?: 'default';
+$value = $input ?? 'default';
```

If an explicit condition is needed, use the full ternary operator:

```diff-php
-$value = $input ?: 'default';
+$value = $input !== '' ? $input : 'default';
```
