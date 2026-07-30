---
title: "switch.alwaysTrue"
shortDescription: "A switch case always matches, making the cases below it unreachable."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @param 1|2 $i */
function doFoo(int $i): void
{
	switch ($i) {
		case 1:
			break;
		case 2:
			break;
		case 3:
			break;
	}
}
```

## Why is it reported?

The comparison between the `switch` subject and this case value is always `true`. Once the subject's possible values are fully covered by the cases above, PHPStan knows one of them will always match — so any case listed afterwards can never be reached.

In the example above `$i` is always `1` or `2`, so by the time execution could fall through to `case 3` the subject has already matched. The same happens after every member of an enum has been covered:

```php
<?php declare(strict_types = 1);

enum Suit
{
	case Hearts;
	case Diamonds;
}

function doFoo(Suit $suit): void
{
	switch ($suit) {
		case Suit::Hearts:
			break;
		case Suit::Diamonds:
			break;
		case Suit::Hearts: // always true, unreachable
			break;
	}
}
```

A case that always matches is only reported when other cases follow it. The last case of a `switch` is allowed to be always-true, since nothing below it becomes unreachable.

## How to fix it

Remove the unreachable cases that follow the exhaustive one:

```diff-php
 switch ($i) {
 	case 1:
 		break;
 	case 2:
 		break;
-	case 3:
-		break;
 }
```

If one of the earlier cases was meant to match a different value, correct it so the subject is no longer fully covered before reaching the later case.
