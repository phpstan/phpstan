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

The `@pure-unless-parameter-passed` tag marks a method as pure *unless* an argument is passed for the named parameter. It is meant for optional by-reference out-parameters: writing into the caller's variable through such a parameter is the side effect that makes the call impure.

A parameter passed by value cannot produce a side effect just by being passed — the method only receives a copy. The tag therefore has no meaning on a by-value parameter. This mirrors [`pureFunction.parameterPassedNotByRef`](/error-identifiers/pureFunction.parameterPassedNotByRef) for functions.

## How to fix it

If the method has no side effects, mark it as pure instead:

```diff-php
 	/**
-	 * @pure-unless-parameter-passed $flag
+	 * @phpstan-pure
 	 */
 	public function replaceByValue(string $subject, bool $flag = false): string
```

If the parameter is meant to be an out-parameter, declare it by reference and describe the written type with `@param-out`:

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
