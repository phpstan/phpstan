---
title: "enum.debugInfoNotSupported"
shortDescription: "Enum declares the __debugInfo() magic method, which is supported only on PHP 8.6 and later."
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

enum Suit
{
	case Hearts;
	case Spades;

	public function __debugInfo(): array
	{
		return ['name' => $this->name];
	}
}
```

## Why is it reported?

Before PHP 8.6, enums cannot declare the `__debugInfo()` magic method. Loading such an enum on an older PHP version is a fatal error: `Enum Suit cannot include magic method __debugInfo`. PHP 8.6 lifted this restriction, so the method is allowed only when the analysed code targets PHP 8.6 or later.

## How to fix it

If the project requires PHP 8.6 or later, configure PHPStan to analyse the code for it by setting the [`phpVersion`](/config-reference#phpversion) option.

If older PHP versions must be supported, remove the method and expose the debug information through a regular method instead:

```diff-php
 enum Suit
 {
 	case Hearts;
 	case Spades;

-	public function __debugInfo(): array
+	public function toDebugArray(): array
 	{
 		return ['name' => $this->name];
 	}
 }
```

Or declare the enum with `__debugInfo()` only when running on PHP 8.6 or later:

```php
<?php declare(strict_types = 1);

if (PHP_VERSION_ID >= 80600) {
	enum Suit
	{
		case Hearts;
		case Spades;

		public function __debugInfo(): array
		{
			return ['name' => $this->name];
		}
	}
}
```
