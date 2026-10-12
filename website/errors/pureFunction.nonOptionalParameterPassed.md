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
function replaceAll(string $subject, int &$count): string
{
	$count = 1;

	return $subject;
}
```

## Why is it reported?

The `@pure-unless-parameter-passed` tag marks a function as pure *unless* the named by-reference parameter is passed by the caller. Writing to a by-reference out parameter is a side effect on the caller's variable, so the function is pure only for calls that omit that argument, like PHP's `str_replace()` without its `$count` argument.

Here `$count` has no default value, so every call has to pass it. The case where the function is pure can never happen, so the function is never pure and the tag only makes it look as if it could be.

## How to fix it

Make the parameter optional by giving it a default value, so callers can omit it and get a pure call:

```diff-php
 /**
  * @param-out int $count
  * @pure-unless-parameter-passed $count
  */
-function replaceAll(string $subject, int &$count): string
+function replaceAll(string $subject, int &$count = 0): string
 {
```

If the parameter has to stay required, the function is always impure. Remove the tag:

```diff-php
 /**
  * @param-out int $count
- * @pure-unless-parameter-passed $count
  */
 function replaceAll(string $subject, int &$count): string
```
