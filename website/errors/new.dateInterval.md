---
title: "new.dateInterval"
shortDescription: "Invalid duration string passed to the DateInterval constructor."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

new DateInterval('1M');
```

## Why is it reported?

The duration string passed to the `DateInterval` constructor is invalid and produces an error at runtime. PHP's `DateInterval` constructor expects an [ISO 8601 duration](https://en.wikipedia.org/wiki/ISO_8601#Durations) string starting with the letter `P`.

In the example above, `'1M'` is missing the required `P` prefix. The correct form is `'P1M'` for one month or `'PT1M'` for one minute.

## How to fix it

Use a valid ISO 8601 duration format:

```diff-php
-new DateInterval('1M');
+new DateInterval('P1M');
```

Valid duration strings always start with `P` and follow this structure:

- `'P1Y'` — 1 year
- `'P1M'` — 1 month
- `'P1D'` — 1 day
- `'PT1H'` — 1 hour
- `'PT1M'` — 1 minute
- `'PT1S'` — 1 second
- `'P1Y2M3DT4H5M6S'` — combined duration

The `T` separator is required before time components (hours, minutes, seconds).
