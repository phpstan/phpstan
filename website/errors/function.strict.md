---
title: "function.strict"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function findItem(string $needle, array $haystack): bool
{
	return in_array($needle, $haystack);
}
```

This rule is provided by the package [`phpstan/phpstan-strict-rules`](https://github.com/phpstan/phpstan-strict-rules).

## Why is it reported?

Certain PHP functions perform loose comparison by default, which can lead to unexpected results due to type juggling. The strict rules package requires the strict comparison parameter to be explicitly set to `true` for the following functions:

- `in_array()` -- parameter #3 (`$strict`)
- `array_search()` -- parameter #3 (`$strict`)
- `array_keys()` -- parameter #3 (`$strict`), when searching for a value
- `base64_decode()` -- parameter #2 (`$strict`)

Without strict mode, `in_array(0, ['foo'])` returns `true` because `0 == 'foo'` under loose comparison. This is almost never the intended behaviour.

## How to fix it

Pass `true` as the strict comparison parameter:

```diff-php
 <?php declare(strict_types = 1);

 function findItem(string $needle, array $haystack): bool
 {
-	return in_array($needle, $haystack);
+	return in_array($needle, $haystack, true);
 }
```

The same applies to `array_search()`:

```diff-php
 <?php declare(strict_types = 1);

 /** @param array<string, mixed> $data */
 function findKey(mixed $value, array $data): string|false
 {
-	return array_search($value, $data);
+	return array_search($value, $data, true);
 }
```
