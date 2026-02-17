---
title: "enum.magicMethod"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

enum Suit: string
{
	case Hearts = 'hearts';
	case Diamonds = 'diamonds';

	public function __get(string $name): mixed
	{
		return null;
	}
}
```

## Why is it reported?

PHP enums are not allowed to contain most magic methods. Only a limited set of magic methods is permitted in enums (`__call`, `__callStatic`, `__invoke`). Methods like `__get`, `__set`, `__isset`, `__unset`, `__serialize`, `__unserialize`, `__toString`, `__clone`, and others are forbidden because enums are not meant to be modified or have their behavior customized in ways that could break value semantics.

## How to fix it

Remove the disallowed magic method from the enum and use a regular named method instead:

```diff-php
 <?php declare(strict_types = 1);

 enum Suit: string
 {
 	case Hearts = 'hearts';
 	case Diamonds = 'diamonds';

-	public function __get(string $name): mixed
-	{
-		return null;
-	}
+	public function getLabel(): string
+	{
+		return match($this) {
+			self::Hearts => 'Hearts',
+			self::Diamonds => 'Diamonds',
+		};
+	}
 }
```
