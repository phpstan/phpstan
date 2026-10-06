---
title: "pureFunction.nonOptionalParameterPassed"
shortDescription: "Function is marked @pure-unless-parameter-passed for a parameter that is not optional."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @param-out int $count
 * @pure-unless-parameter-passed $count
 */
function myReplace(string $subject, int &$count): string // ERROR: Function myReplace() is marked @pure-unless-parameter-passed for parameter $count, but $count is not optional, so function myReplace() is never pure.
{
	$count = 1;

	return $subject;
}
```

## Why is it reported?

The `@pure-unless-parameter-passed` tag marks a function as pure *unless* the named parameter is passed by the caller. Calls that omit the argument are treated as pure, while calls that pass it are treated as impure.

Here `$count` has no default value, so every call must pass it. The condition under which the function would be pure can never happen, so the tag has no effect and the function is effectively always impure. This usually means a default value was forgotten.

## How to fix it

Make the parameter optional by giving it a default value, so callers can omit it and benefit from the function being pure:

```diff-php
 /**
  * @param-out int $count
  * @pure-unless-parameter-passed $count
  */
-function myReplace(string $subject, int &$count): string
+function myReplace(string $subject, int &$count = 0): string
 {
 	$count = 1;

 	return $subject;
 }
```

If the parameter is meant to be required, remove the tag, because the function is never pure:

```diff-php
 /**
  * @param-out int $count
- * @pure-unless-parameter-passed $count
  */
 function myReplace(string $subject, int &$count): string
```
