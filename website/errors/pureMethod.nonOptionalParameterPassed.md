---
title: "pureMethod.nonOptionalParameterPassed"
shortDescription: "Method is marked @pure-unless-parameter-passed for a required parameter, so it can never be pure."
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

The `@pure-unless-parameter-passed` tag marks a method as pure *unless* the caller passes the named parameter. Calls that omit the argument are treated as pure, and calls that pass it are treated as impure because the method writes to the caller's variable through the reference.

Here `$count` has no default value, so every call must pass it. The condition that would make the method pure can never be met, so the tag has no effect and only misleads readers into thinking the method can be called purely.

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

If the parameter must stay required, the method is always impure. Remove the tag:

```diff-php
 	/**
 	 * @param-out int $count
-	 * @pure-unless-parameter-passed $count
 	 */
 	public function replace(string $subject, int &$count): string
```
