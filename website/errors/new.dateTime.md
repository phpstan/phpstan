---
title: "new.dateTime"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

new DateTime('2020.11.17');
```

## Why is it reported?

The date string passed to the `DateTime` constructor is invalid and produces an error at runtime. PHP's `DateTime` (and `DateTimeImmutable`) constructors throw an exception or produce warnings when they cannot parse the given date string.

In the example above, `'2020.11.17'` uses dots as separators which PHP interprets differently than intended, leading to a parsing error.

## How to fix it

Use a valid date format:

```diff-php
-new DateTime('2020.11.17');
+new DateTime('2020-11-17');
```

Common valid date formats include:

- `'2020-11-17'` (ISO 8601)
- `'2020-11-17 14:30:00'` (date and time)
- `'now'` (current date/time)
- `'yesterday'`, `'tomorrow'` (relative dates)

For custom formats, use `DateTime::createFromFormat()`:

```diff-php
-new DateTime('2020.11.17');
+DateTime::createFromFormat('Y.m.d', '2020.11.17');
```
