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
function myReplace(string $subject, int &$count): string
{
	$count = 1;

	return $subject;
}
```

## Why is it reported?

The `@pure-unless-parameter-passed` tag marks a function as pure *unless* an argument is passed for the named by-reference parameter. This mirrors functions like `str_replace()`, which only write to the outside world through the optional `&$count` parameter when the caller provides it. When the argument is omitted, the call is pure.

Here `$count` has no default value, so every call has to pass it. The condition that would keep the function pure can never be met, which means the function is never pure and the tag is misleading.

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

If the parameter is meant to be required, remove the tag. The function writes through a by-reference parameter, so it is not pure:

```diff-php
 /**
  * @param-out int $count
- * @pure-unless-parameter-passed $count
  */
 function myReplace(string $subject, int &$count): string
```
