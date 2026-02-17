---
title: "enum.backingType"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

enum Priority: float // error
{
	case Low = 0.5;
	case High = 1.0;
}
```

## Why is it reported?

PHP backed enums only support `int` or `string` as their backing type. Any other type such as `float`, `bool`, or `array` is not allowed by the language. This is a hard constraint enforced by the PHP engine.

## How to fix it

Change the backing type to either `int` or `string`:

```diff-php
 <?php declare(strict_types = 1);

-enum Priority: float
+enum Priority: int
 {
-	case Low = 0.5;
-	case High = 1.0;
+	case Low = 1;
+	case High = 2;
 }
```

If you need to associate non-integer/non-string values with enum cases, use a method instead:

```php
<?php declare(strict_types = 1);

enum Priority: string
{
	case Low = 'low';
	case High = 'high';

	public function weight(): float
	{
		return match ($this) {
			self::Low => 0.5,
			self::High => 1.0,
		};
	}
}
```
