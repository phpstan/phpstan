---
title: "pureMethod.nonOptionalParameterPassed"
shortDescription: "Method is marked @pure-unless-parameter-passed for a parameter that is not optional."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Replacer
{

	/**
	 * @param-out int $count
	 * @pure-unless-parameter-passed $count
	 */
	public function replace(string $subject, int &$count): string
	{
		$count = 1;

		return $subject;
	}

}
```

## Why is it reported?

The `@pure-unless-parameter-passed` tag marks a method as pure *unless* the named by-reference parameter is passed by the caller. Writing to a by-reference out parameter is a side effect on the caller's variable, so the method is pure only for calls that omit that argument, like PHP's `str_replace()` without its `$count` argument.

Here `$count` has no default value, so every call has to pass it. The case where the method is pure can never happen, so the method is never pure and the tag only makes it look as if it could be. This mirrors [`pureFunction.nonOptionalParameterPassed`](/error-identifiers/pureFunction.nonOptionalParameterPassed) for functions.

## How to fix it

Make the parameter optional by giving it a default value, so callers can omit it and get a pure call:

```diff-php
 	/**
 	 * @param-out int $count
 	 * @pure-unless-parameter-passed $count
 	 */
-	public function replace(string $subject, int &$count): string
+	public function replace(string $subject, int &$count = 0): string
 	{
```

If the parameter has to stay required, the method is always impure. Remove the tag:

```diff-php
 	/**
 	 * @param-out int $count
-	 * @pure-unless-parameter-passed $count
 	 */
 	public function replace(string $subject, int &$count): string
```
