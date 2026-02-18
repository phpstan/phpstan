---
title: "enum.methodRedeclaration"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

enum Suit
{
	case Hearts;
	case Diamonds;

	public static function cases(): array
	{
		return [];
	}
}
```

## Why is it reported?

Enums in PHP provide built-in methods that cannot be redeclared. The `cases()` method is always available on every enum, and backed enums additionally have `from()` and `tryFrom()` methods. Attempting to define these methods manually results in a fatal error because they are provided natively by the language.

## How to fix it

Remove the redeclared method and use the native implementation:

```diff-php
 enum Suit
 {
 	case Hearts;
 	case Diamonds;
-
-	public static function cases(): array
-	{
-		return [];
-	}
 }
```

If custom logic is needed, use a method with a different name:

```diff-php
 enum Suit
 {
 	case Hearts;
 	case Diamonds;

-	public static function cases(): array
+	public static function availableCases(): array
 	{
-		return [];
+		return [self::Hearts];
 	}
 }
```
