---
title: "classImplements.enum"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

enum Suit
{
	case Hearts;
	case Diamonds;
}

class Card implements Suit // error
{
}
```

## Why is it reported?

In PHP, a class can only implement interfaces. Enums are not interfaces and cannot be used in an `implements` clause. This is a fundamental language constraint -- the `implements` keyword is reserved exclusively for interface types.

## How to fix it

Extract an interface that the enum implements, then have your class implement that interface instead:

```diff-php
 <?php declare(strict_types = 1);

+interface HasLabel
+{
+	public function label(): string;
+}
+
-enum Suit
+enum Suit implements HasLabel
 {
 	case Hearts;
 	case Diamonds;
+
+	public function label(): string
+	{
+		return $this->name;
+	}
 }

-class Card implements Suit
+class Card implements HasLabel
 {
+	public function label(): string
+	{
+		return 'Joker';
+	}
 }
```
