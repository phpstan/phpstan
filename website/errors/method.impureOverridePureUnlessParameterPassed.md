---
title: "method.impureOverridePureUnlessParameterPassed"
shortDescription: "Impure method overrides a parent method marked @pure-unless-parameter-passed."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

interface PureUnlessParent
{

	/**
	 * @param-out int $count
	 * @pure-unless-parameter-passed $count
	 */
	public function replace(string $subject, int &$count = 0): string;

}

class ImpureChild implements PureUnlessParent
{

	/**
	 * @phpstan-impure
	 */
	public function replace(string $subject, int &$count = 0): string // ERROR: Impure method ImpureChild::replace() overrides method PureUnlessParent::replace() marked @pure-unless-parameter-passed.
	{
		echo 'side effect';
		$count = 1;

		return $subject;
	}

}
```

## Why is it reported?

The `@pure-unless-parameter-passed` tag declares that the parent method is pure *unless* the named by-reference parameter is passed by the caller. When the caller omits that argument, the call has no side effects and its result can be relied upon like any other pure call.

An overriding method marked `@phpstan-impure` (or declared in a class marked `@phpstan-all-methods-impure`) breaks this contract: it has side effects even when the flagged parameter is not passed. Code that calls the parent method without the flagged argument and treats it as pure would then be reasoning about a method that is always impure.

This check is enabled by the `reportMethodPurityOverride` option, which is part of [Bleeding Edge](/blog/what-is-bleeding-edge).

## How to fix it

Remove `@phpstan-impure` from the child and keep its body free of side effects other than writing to the flagged by-reference parameter, so it honors the inherited `@pure-unless-parameter-passed` contract:

```diff-php
 class ImpureChild implements PureUnlessParent
 {

-	/**
-	 * @phpstan-impure
-	 */
 	public function replace(string $subject, int &$count = 0): string
 	{
-		echo 'side effect';
 		$count = 1;

 		return $subject;
 	}

 }
```

If the child genuinely needs side effects, the parent's purity contract cannot hold across all implementations. Remove `@pure-unless-parameter-passed` from the parent method:

```diff-php
 interface PureUnlessParent
 {

 	/**
 	 * @param-out int $count
-	 * @pure-unless-parameter-passed $count
 	 */
 	public function replace(string $subject, int &$count = 0): string;

 }
```
