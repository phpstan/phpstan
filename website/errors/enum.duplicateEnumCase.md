---
title: "enum.duplicateEnumCase"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

enum Suit
{
	case Hearts;
	case Diamonds;
	case Hearts;
}
```

## Why is it reported?

An enum declares two cases with the same name. Each enum case must have a unique name within the enum. PHP does not allow redeclaring an enum case, just as it does not allow redeclaring a class constant.

## How to fix it

Remove the duplicate case or rename it:

```diff-php
 enum Suit
 {
 	case Hearts;
 	case Diamonds;
-	case Hearts;
+	case Clubs;
 }
```
