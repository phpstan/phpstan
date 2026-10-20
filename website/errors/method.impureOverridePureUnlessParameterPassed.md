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

The `@pure-unless-parameter-passed` tag declares that the parent method is pure *unless* an argument is passed for the named by-reference parameter. When the caller omits that argument, the call has no side effects; when the caller passes it, the method writes into the caller's variable. Apart from writing through that parameter, the method body is expected to have no side effects.

An overriding method marked `@phpstan-impure` (or living in a class marked `@phpstan-all-methods-impure`) breaks this contract: it has side effects even when the by-reference argument is omitted. Code that relies on the parent's conditional purity — for example calling `replace($subject)` without `$count` inside a pure function — would then be reasoning about a method that is always impure.

This check is part of [Bleeding Edge](/blog/what-is-bleeding-edge).

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
