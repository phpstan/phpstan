---
title: "pureFunction.nonOptionalParameterPassed"
shortDescription: "Function is marked @pure-unless-parameter-passed for a required parameter, so it is never pure."
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

The `@pure-unless-parameter-passed` tag marks a function as pure *unless* an argument is passed for the named by-reference parameter. It describes functions like `preg_replace()` or `str_replace()`, whose optional `$count` out-parameter is the only thing that makes a call impure: calls that omit it are pure, calls that pass it write into the caller's variable.

Here `$count` has no default value, so every call has to pass it. The condition under which the function would be pure can never be met, which makes the tag meaningless — the function is effectively always impure.

## How to fix it

Make the parameter optional by giving it a default value, so callers can omit it and get a pure call:

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

If the parameter has to stay required, the function is never pure. Remove the tag:

```diff-php
 /**
  * @param-out int $count
- * @pure-unless-parameter-passed $count
  */
 function myReplace(string $subject, int &$count): string
```
