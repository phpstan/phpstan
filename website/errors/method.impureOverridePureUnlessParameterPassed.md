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

The `@pure-unless-parameter-passed` tag declares that the parent method is pure *unless* the named optional by-reference parameter is passed. When the caller omits that argument, the call has no side effects; when the caller passes it, the method writes into the caller's variable, which makes the call impure.

An overriding method marked `@phpstan-impure` (or declared in a class marked `@phpstan-all-methods-impure`) breaks this contract: it has side effects regardless of whether the argument is passed. Code that calls the parent method without the by-reference argument and relies on it being pure would then be reasoning about a method that is always impure.

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
