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

The `@pure-unless-parameter-passed` tag marks a method as pure *unless* the named parameter is passed by the caller. Calls that omit the argument are treated as pure, while calls that pass it are treated as impure.

Here `$count` has no default value, so every call must pass it. The condition under which the method would be pure can never happen, so the tag has no effect and the method is effectively always impure. This usually means a default value was forgotten.

## How to fix it

Make the parameter optional by giving it a default value, so callers can omit it and benefit from the method being pure:

```diff-php
 	/**
 	 * @param-out int $count
 	 * @pure-unless-parameter-passed $count
 	 */
-	public function replace(string $subject, int &$count): string
+	public function replace(string $subject, int &$count = 0): string
```

If the parameter is meant to be required, remove the tag, because the method is never pure:

```diff-php
 	/**
 	 * @param-out int $count
-	 * @pure-unless-parameter-passed $count
 	 */
 	public function replace(string $subject, int &$count): string
```
