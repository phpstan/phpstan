---
title: "method.impureOverridePureUnlessCallable"
shortDescription: "Impure method overrides a parent method marked @pure-unless-callable-is-impure."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

interface PureUnlessParent
{

	/**
	 * @pure-unless-callable-is-impure $cb
	 */
	public function run(callable $cb): int;

}

class ImpureChild implements PureUnlessParent
{

	/**
	 * @phpstan-impure
	 */
	public function run(callable $cb): int // ERROR: Impure method ImpureChild::run() overrides method PureUnlessParent::run() marked @pure-unless-callable-is-impure.
	{
		echo 'side effect';

		return $cb(1);
	}

}
```

## Why is it reported?

The `@pure-unless-callable-is-impure` tag declares that the parent method is pure *except* when the given callable parameter is impure. Its purity is conditional on the callable passed in, but the method's own body is expected to have no other side effects.

An overriding method marked `@phpstan-impure` breaks this contract: it introduces side effects regardless of the callable. Code that relies on the parent's conditional purity — for example when the parent is called with a pure callable — would then be reasoning about a method that is always impure. The override is therefore incompatible with the inherited purity guarantee.

## How to fix it

Remove `@phpstan-impure` from the child and keep its body free of side effects other than invoking the flagged callable, so it honors the inherited `@pure-unless-callable-is-impure` contract:

```diff-php
 class ImpureChild implements PureUnlessParent
 {

-	/**
-	 * @phpstan-impure
-	 */
 	public function run(callable $cb): int
 	{
-		echo 'side effect';
-
 		return $cb(1);
 	}

 }
```

If the child genuinely needs side effects, the parent's purity contract cannot hold across all implementations. Remove `@pure-unless-callable-is-impure` from the parent method:

```diff-php
 interface PureUnlessParent
 {

-	/**
-	 * @pure-unless-callable-is-impure $cb
-	 */
 	public function run(callable $cb): int;

 }
```
