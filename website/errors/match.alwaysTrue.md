---
title: "match.alwaysTrue"
shortDescription: "Match arm condition always matches, making subsequent arms unreachable."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $i): string
{
	$flag = true;
	return match (true) {
		$flag => 'always',
		$i > 0 => 'positive',
		default => 'other',
	};
}
```

## Why is it reported?

A match arm comparison always evaluates to `true`, which means all subsequent arms are unreachable. In the example above, `$flag` is always `true`, so the first arm always matches when compared to the match subject `true`, making the remaining arms dead code.

### Exhaustive enum matches with a `default` arm

This error is also reported when a `match` expression covers all enum cases and also has a `default` arm:

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
		Suit::Spades => 'black', // match.alwaysTrue: always true
		default => throw new \LogicException('Unknown suit'),
	};
}
```

The `Suit::Spades` arm is reported because all four enum cases are already covered before the `default` arm is reached, making the last arm's comparison always true (only `Suit::Spades` can reach it). The error tip says: *"Remove remaining cases below this one and this error will disappear too."*

PHPStan discourages having a `default` arm in exhaustive enum matches on purpose. Without the `default` arm, PHPStan reports [`match.unhandled`](/error-identifiers/match.unhandled) when a new case is added to the enum. This forces you to handle every case explicitly, which is much safer. With a `default` arm present, new enum cases would silently fall through to the `default` and the mistake could go unnoticed.

## How to fix it

Remove the unreachable arms:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $i): string
 {
-	$flag = true;
-	return match (true) {
-		$flag => 'always',
-		$i > 0 => 'positive',
-		default => 'other',
-	};
+	return 'always';
 }
```

Or fix the logic so the match arm condition can vary:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(int $i): string
+function doFoo(int $i, bool $flag): string
 {
-	$flag = true;
 	return match (true) {
 		$flag => 'flagged',
 		$i > 0 => 'positive',
 		default => 'other',
 	};
 }
```

For exhaustive enum matches, remove the `default` arm:

```diff-php
 return match ($suit) {
 	Suit::Hearts, Suit::Diamonds => 'red',
 	Suit::Clubs => 'black',
 	Suit::Spades => 'black',
-	default => throw new \LogicException('Unknown suit'),
 };
```

This way, when a new case like `Suit::Jokers` is added to the enum, PHPStan will report [`match.unhandled`](/error-identifiers/match.unhandled), reminding you to handle it.

By default this error is not reported when the always-true condition is the last one before `default`. This can be changed with the [`reportAlwaysTrueInLastCondition`](/config-reference#reportalwaystrueinlastcondition) configuration option.
