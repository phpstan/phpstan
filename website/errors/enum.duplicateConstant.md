---
title: "enum.duplicateConstant"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1); // lint >= 8.1

enum Suit
{
	const JOKER = 'joker';
	const JOKER = 'jester';
}
```

## Why is it reported?

An enum (like a class) cannot declare two constants with the same name. PHP will produce a fatal error when it encounters a duplicate constant declaration. This applies to both regular class constants and enum case names sharing the same namespace within the enum.

## How to fix it

Remove the duplicate constant declaration or rename one of the constants:

```diff-php
 enum Suit
 {
 	const JOKER = 'joker';
-	const JOKER = 'jester';
+	const JESTER = 'jester';
 }
```
