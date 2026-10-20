---
title: "pureMethod.nonOptionalParameterPassed"
shortDescription: "Method is marked @pure-unless-parameter-passed for a required parameter, so it is never pure."
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

The `@pure-unless-parameter-passed` tag marks a method as pure *unless* an argument is passed for the named by-reference parameter. Calls that omit the argument are pure, calls that pass it write into the caller's variable.

Here `$count` has no default value, so every call has to pass it. The condition under which the method would be pure can never be met, which makes the tag meaningless — the method is effectively always impure. This mirrors [`pureFunction.nonOptionalParameterPassed`](/error-identifiers/pureFunction.nonOptionalParameterPassed) for functions.

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

If the parameter has to stay required, the method is never pure. Remove the tag:

```diff-php
 	/**
 	 * @param-out int $count
-	 * @pure-unless-parameter-passed $count
 	 */
 	public function replace(string $subject, int &$count): string
```
