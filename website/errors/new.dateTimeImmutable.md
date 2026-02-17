---
title: "new.dateTimeImmutable"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$date = new DateTimeImmutable('this is not a date');
```

## Why is it reported?

The `DateTimeImmutable` constructor is being called with a date string that PHP cannot parse. When an invalid date string is passed, `DateTimeImmutable::__construct()` produces an error. PHPStan detects constant string arguments that will cause a parsing failure at runtime and reports them at analysis time.

## How to fix it

Provide a valid date string:

```diff-php
 <?php declare(strict_types = 1);

-$date = new DateTimeImmutable('this is not a date');
+$date = new DateTimeImmutable('2024-01-15');
```

Or use `DateTimeImmutable::createFromFormat()` for predictable parsing of non-standard formats:

```diff-php
 <?php declare(strict_types = 1);

-$date = new DateTimeImmutable('this is not a date');
+$date = DateTimeImmutable::createFromFormat('Y/m/d', '2024/01/15');
```
