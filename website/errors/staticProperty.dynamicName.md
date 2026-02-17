---
title: "staticProperty.dynamicName"
ignorable: true
---

This error is reported by `phpstan/phpstan-strict-rules`.

## Code example

```php
<?php declare(strict_types = 1);

class Registry
{
	public static string $name = 'default';

	public static function get(string $property): string
	{
		return Registry::$$property;
	}
}
```

## Why is it reported?

Accessing a static property with a dynamic (variable) name makes the code harder to follow and analyse statically. PHPStan cannot verify that the variable holds a valid property name, which may lead to runtime errors that go undetected during analysis.

## How to fix it

Replace the variable static property access with explicit property references.

```diff-php
 <?php declare(strict_types = 1);

 class Registry
 {
 	public static string $name = 'default';

-	public static function get(string $property): string
+	public static function get(string $property): ?string
 	{
-		return Registry::$$property;
+		return match ($property) {
+			'name' => Registry::$name,
+			default => null,
+		};
 	}
 }
```
