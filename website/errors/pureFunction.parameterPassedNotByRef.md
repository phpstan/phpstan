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
function process(string $subject, bool $flag = false): string
{
	return $subject;
}
```

## Why is it reported?

The `@pure-unless-parameter-passed` tag marks a function as pure *unless* an argument is passed for the named parameter. It is meant for optional by-reference "out" parameters, like the `&$count` parameter of `str_replace()`: writing a value back to the caller's variable is the only side effect, and it only happens when the caller passes that argument.

A parameter passed by value cannot affect anything outside the function. Passing or omitting it does not change whether the call has side effects — if the function is impure, it is impure regardless of the argument. The tag therefore has no meaning on a by-value parameter.

## How to fix it

If the function has no side effects, replace the tag with `@phpstan-pure`:

```diff-php
 /**
- * @pure-unless-parameter-passed $flag
+ * @phpstan-pure
  */
 function process(string $subject, bool $flag = false): string
 {
 	return $subject;
 }
```

If the parameter is supposed to be an out parameter that the function writes to, declare it as passed by reference:

```diff-php
 /**
+ * @param-out int $count
- * @pure-unless-parameter-passed $flag
+ * @pure-unless-parameter-passed $count
  */
-function process(string $subject, bool $flag = false): string
+function process(string $subject, int &$count = 0): string
 {
+	$count = 1;
+
 	return $subject;
 }
```
