---
title: "method.impureOverridePureUnlessParameterPassed"
shortDescription: "Impure method overrides a parent method marked @pure-unless-parameter-passed."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

interface Replacer
{

	/**
	 * @param-out int $count
	 * @pure-unless-parameter-passed $count
	 */
	public function replace(string $subject, int &$count = 0): string;

}

class LoggingReplacer implements Replacer
{

	/**
	 * @phpstan-impure
	 */
	public function replace(string $subject, int &$count = 0): string // ERROR: Impure method LoggingReplacer::replace() overrides method Replacer::replace() marked @pure-unless-parameter-passed.
	{
		echo 'Replacing...';
		$count = 1;

		return $subject;
	}

}
```

## Why is it reported?

The `@pure-unless-parameter-passed` tag declares that the parent method is pure *unless* the caller passes the named by-reference parameter. Calls that omit `$count` are expected to have no side effects.

An overriding method marked `@phpstan-impure` breaks this contract: it has side effects even when `$count` is not passed. Code that calls `Replacer::replace()` without `$count` and relies on the call being pure would be wrong when the object is a `LoggingReplacer`.

This check is enabled with [bleeding edge](/blog/what-is-bleeding-edge).

## How to fix it

Remove `@phpstan-impure` from the child and keep its body free of side effects other than writing to the flagged by-reference parameter:

```diff-php
 class LoggingReplacer implements Replacer
 {

-	/**
-	 * @phpstan-impure
-	 */
 	public function replace(string $subject, int &$count = 0): string
 	{
-		echo 'Replacing...';
 		$count = 1;

 		return $subject;
 	}

 }
```

If the child really needs side effects, the parent's purity contract cannot hold for all implementations. Remove `@pure-unless-parameter-passed` from the parent method:

```diff-php
 interface Replacer
 {

 	/**
 	 * @param-out int $count
-	 * @pure-unless-parameter-passed $count
 	 */
 	public function replace(string $subject, int &$count = 0): string;

 }
```
