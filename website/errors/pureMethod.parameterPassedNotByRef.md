---
title: "pureMethod.parameterPassedNotByRef"
shortDescription: "Method is marked @pure-unless-parameter-passed for a parameter that is not passed by reference."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Normalizer
{

	/**
	 * @pure-unless-parameter-passed $trim
	 */
	public function normalize(string $subject, bool $trim = false): string
	{
		return $trim ? trim($subject) : $subject;
	}

}
```

## Why is it reported?

The `@pure-unless-parameter-passed` tag marks a method as pure *unless* the named parameter is passed by the caller. It is meant for optional by-reference out parameters, like the `$count` parameter of PHP's `str_replace()`: writing to the caller's variable is the only side effect, and it happens only when the argument is passed.

A by-value parameter like `$trim` cannot change anything outside the method, so passing it cannot make a call impure. The method is either pure for every call, or its body has side effects that happen whether or not the argument is passed. Either way, the tag says nothing that can be checked. This mirrors [`pureFunction.parameterPassedNotByRef`](/error-identifiers/pureFunction.parameterPassedNotByRef) for functions.

## How to fix it

If the method has no side effects, replace the tag with `@phpstan-pure`:

```diff-php
 	/**
-	 * @pure-unless-parameter-passed $trim
+	 * @phpstan-pure
 	 */
 	public function normalize(string $subject, bool $trim = false): string
```

If the method is meant to write to the caller's variable, make the parameter an optional by-reference out parameter:

```diff-php
 	/**
-	 * @pure-unless-parameter-passed $trim
+	 * @param-out int $count
+	 * @pure-unless-parameter-passed $count
 	 */
-	public function normalize(string $subject, bool $trim = false): string
+	public function normalize(string $subject, int &$count = 0): string
 	{
-		return $trim ? trim($subject) : $subject;
+		$trimmed = trim($subject);
+		$count = strlen($subject) - strlen($trimmed);
+
+		return $trimmed;
 	}
```
