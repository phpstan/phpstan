---
title: "pureFunction.parameterPassedNotByRef"
shortDescription: "Function is marked @pure-unless-parameter-passed for a parameter that is not passed by reference."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @pure-unless-parameter-passed $flag
 */
function format(string $subject, bool $flag = false): string // ERROR: Function format() is marked @pure-unless-parameter-passed for parameter $flag, but $flag is not passed by reference.
{
	return $subject;
}
```

## Why is it reported?

The `@pure-unless-parameter-passed` tag marks a function as pure *unless* an argument is passed for the named parameter. It is meant for optional by-reference out-parameters, like the `$count` parameter of `str_replace()`: writing into the caller's variable through such a parameter is the side effect that makes the call impure.

A parameter passed by value cannot produce a side effect just by being passed — the function only receives a copy. If the function had side effects when `$flag` is passed, they would have to come from its body, and those side effects are not conditional on the argument in a way that can be described or verified. The tag therefore has no meaning on a by-value parameter.

## How to fix it

If the function has no side effects, mark it as pure instead:

```diff-php
 /**
- * @pure-unless-parameter-passed $flag
+ * @phpstan-pure
  */
 function format(string $subject, bool $flag = false): string
```

If the parameter is meant to be an out-parameter, declare it by reference and describe the written type with `@param-out`:

```diff-php
 /**
+ * @param-out int $count
- * @pure-unless-parameter-passed $flag
+ * @pure-unless-parameter-passed $count
  */
-function format(string $subject, bool $flag = false): string
+function format(string $subject, int &$count = 0): string
 {
+	$count = 1;
+
 	return $subject;
 }
```
