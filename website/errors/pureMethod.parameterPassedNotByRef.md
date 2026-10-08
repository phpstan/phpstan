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
	public function replace(string $subject, bool $flag = false): string // ERROR: Method Replacer::replace() is marked @pure-unless-parameter-passed for parameter $flag, but $flag is not passed by reference.
	{
		return $subject;
	}

}
```

## Why is it reported?

The `@pure-unless-parameter-passed` tag marks a method as pure *unless* the caller passes the named parameter. It is meant for optional by-reference "out" parameters, like the `$count` parameter of `str_replace()`: writing to such a parameter changes the caller's variable, so passing it makes the call impure, while omitting it keeps the call pure.

A parameter passed by value cannot cause a side effect on its own — the method only receives a copy. If the method were impure, that impurity would come from its body and would not depend on whether the argument was passed. The tag therefore cannot express anything meaningful for a by-value parameter.

## How to fix it

If the method has no side effects, mark it as pure instead:

```diff-php
 	/**
-	 * @pure-unless-parameter-passed $flag
+	 * @phpstan-pure
 	 */
 	public function replace(string $subject, bool $flag = false): string
```

If the parameter is meant to return a value to the caller, make it an optional by-reference parameter:

```diff-php
 	/**
-	 * @pure-unless-parameter-passed $flag
+	 * @param-out bool $flag
+	 * @pure-unless-parameter-passed $flag
 	 */
-	public function replace(string $subject, bool $flag = false): string
+	public function replace(string $subject, bool &$flag = false): string
 	{
+		$flag = true;
+
 		return $subject;
 	}
```
