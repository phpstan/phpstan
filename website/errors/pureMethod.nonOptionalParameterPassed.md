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
	public function replace(string $subject, int &$count): string // ERROR: Method Replacer::replace() is marked @pure-unless-parameter-passed for parameter $count, but $count is not optional, so method Replacer::replace() is never pure.
	{
		$count = 1;

		return $subject;
	}

}
```

## Why is it reported?

The `@pure-unless-parameter-passed` tag marks a method as pure *unless* the named parameter is passed. Calls that omit the argument are pure, and calls that pass it are impure. This is how functions like `str_replace()` behave: they have no side effects unless the optional by-reference `$count` argument is supplied.

Here `$count` has no default value, so every call must pass it. The condition that would keep the method pure can never happen, which makes the tag meaningless: the method is always impure.

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

If the parameter must stay required, the method is never pure. Replace the tag with `@phpstan-impure`, or remove it:

```diff-php
 	/**
 	 * @param-out int $count
-	 * @pure-unless-parameter-passed $count
+	 * @phpstan-impure
 	 */
 	public function replace(string $subject, int &$count): string
```
