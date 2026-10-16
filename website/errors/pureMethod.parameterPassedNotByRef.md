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

The `@pure-unless-parameter-passed` tag marks a method as pure *unless* the named parameter is passed. It is meant for optional by-reference "out" parameters, like the `$count` parameter of `str_replace()`: when the caller passes a variable, the method writes to it, which is a side effect visible to the caller.

A parameter passed by value only gives the method a copy of the value. Passing it can never cause a side effect on its own. If passing it changed whether the method is pure, the side effect would have to come from the method body, and the method would be impure whether or not the argument is passed. The tag cannot describe this method correctly.

## How to fix it

If the method has no side effects, remove the tag and mark it `@phpstan-pure`:

```diff-php
 	/**
-	 * @pure-unless-parameter-passed $flag
+	 * @phpstan-pure
 	 */
 	public function replace(string $subject, bool $flag = false): string
```

If the parameter is meant to be an out parameter, declare it by reference and describe the written type with `@param-out`:

```diff-php
 	/**
+	 * @param-out int $count
-	 * @pure-unless-parameter-passed $flag
+	 * @pure-unless-parameter-passed $count
 	 */
-	public function replace(string $subject, bool $flag = false): string
+	public function replace(string $subject, int &$count = 0): string
 	{
+		$count = 1;
+
 		return $subject;
 	}
```
