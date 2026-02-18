---
title: "match.unhandled"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

enum Suit
{
	case Hearts;
	case Diamonds;
	case Clubs;
	case Spades;
}

function suitToColor(Suit $suit): string
{
	return match ($suit) {
		Suit::Hearts, Suit::Diamonds => 'red',
		Suit::Clubs => 'black',
	};
}
```

## Why is it reported?

The `match` expression does not cover all possible values of the subject type. PHP throws an `\UnhandledMatchError` at runtime when no match arm matches the subject value.

In the example above, the `Suit::Spades` case is not handled, so passing it to `suitToColor()` would cause a runtime error.

## How to fix it

Add the missing match arms to handle all possible values:

```diff-php
 return match ($suit) {
 	Suit::Hearts, Suit::Diamonds => 'red',
 	Suit::Clubs => 'black',
+	Suit::Spades => 'black',
 };
```

Alternatively, add a `default` arm to catch any remaining values:

```diff-php
 return match ($suit) {
 	Suit::Hearts, Suit::Diamonds => 'red',
-	Suit::Clubs => 'black',
+	Suit::Clubs, Suit::Spades => 'black',
 };
```
