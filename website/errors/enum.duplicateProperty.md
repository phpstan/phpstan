---
title: "enum.duplicateProperty"
shortDescription: "Property is declared more than once in an enum."
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

enum Status
{
	case Active;

	public int $value;
	public int $value;
}
```

## Why is it reported?

A property with the same name is declared more than once in the same enum. Enums cannot have instance properties in PHP, but PHPStan still detects the duplicate declaration through its generic duplicate-declaration rule that checks all class-like structures uniformly.

## How to fix it

Remove the duplicate property declaration:

```diff-php
 enum Status
 {
 	case Active;

 	public int $value;
-	public int $value;
 }
```
