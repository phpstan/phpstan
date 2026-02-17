---
title: "class.duplicateEnumCase"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

enum Suit: string
{
	case Hearts = 'hearts';
	case Diamonds = 'diamonds';
	case Hearts = 'h';
}
```

## Why is it reported?

An enum declares the same case name more than once. PHP does not allow redeclaring an enum case within the same enum body. This is a fatal error.

In the example above, the case `Hearts` is declared twice in the enum `Suit`.

## How to fix it

Remove the duplicate enum case declaration, keeping only one:

```diff-php
 <?php declare(strict_types = 1);

 enum Suit: string
 {
 	case Hearts = 'hearts';
 	case Diamonds = 'diamonds';
-	case Hearts = 'h';
 }
```

If the duplicate cases were intended to represent different values, rename one of them:

```diff-php
 <?php declare(strict_types = 1);

 enum Suit: string
 {
 	case Hearts = 'hearts';
 	case Diamonds = 'diamonds';
-	case Hearts = 'h';
+	case HeartsShort = 'h';
 }
```
