---
title: "declareStrictTypes.value"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 'foo');
```

## Why is it reported?

The `declare(strict_types=...)` statement only accepts `0` or `1` as its value. Any other value, including strings, floats, or other integers, is invalid. PHP requires this to be either `1` (to enable strict type checking) or `0` (to disable it). This is a hard constraint enforced by the PHP language.

## How to fix it

Use `0` or `1` as the value for `strict_types`:

```diff-php
-<?php declare(strict_types = 'foo');
+<?php declare(strict_types = 1);
```
