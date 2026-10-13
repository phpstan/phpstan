---
title: "pureMethod.parameterPassedNotByRef"
shortDescription: "Method is marked @pure-unless-parameter-passed for a parameter that is not passed by reference."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Replacer
{

	/**
	 * @pure-unless-parameter-passed $flag
	 */
	public function replaceByValue(string $subject, bool $flag = false): string // ERROR: Method Replacer::replaceByValue() is marked @pure-unless-parameter-passed for parameter $flag, but $flag is not passed by reference.
	{
		return $subject;
	}

}
```

## Why is it reported?

The `@pure-unless-parameter-passed` tag marks a method as pure *unless* the named optional parameter is passed. It is meant for by-reference "out" parameters, like the `$count` parameter of `str_replace()`: writing into the caller's variable is a side effect that only happens when the caller passes that argument.

Passing a by-value parameter cannot produce a side effect on its own. The method receives a copy of the value, so the caller's state stays untouched whether the argument is passed or not. For the method to be impure only when `$flag` is passed, the body itself would have to contain a side effect, and that side effect would not be conditional on the argument being passed. The tag therefore does not describe anything that can be verified.

## How to fix it

If the method has no side effects, replace the tag with `@phpstan-pure`:

```diff-php
 	/**
-	 * @pure-unless-parameter-passed $flag
+	 * @phpstan-pure
 	 */
 	public function replaceByValue(string $subject, bool $flag = false): string
```

If the parameter is meant to be an output parameter, declare it as passed by reference and describe its output type with `@param-out`:

```diff-php
 	/**
+	 * @param-out int $count
-	 * @pure-unless-parameter-passed $flag
+	 * @pure-unless-parameter-passed $count
 	 */
-	public function replaceByValue(string $subject, bool $flag = false): string
+	public function replace(string $subject, int &$count = 0): string
 	{
+		$count = 1;
+
 		return $subject;
 	}
```
