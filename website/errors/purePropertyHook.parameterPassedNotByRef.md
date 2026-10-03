---
title: "purePropertyHook.parameterPassedNotByRef"
shortDescription: "Property hook is marked @pure-unless-parameter-passed for a parameter that is not passed by reference."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

final class Counter
{

	public int $count = 0 {
		/**
		 * @pure-unless-parameter-passed $value
		 */
		set (int $value) {
			$this->count = $value;
		}
	}

}
```

## Why is it reported?

The `@pure-unless-parameter-passed` tag marks a function as pure *unless* an argument is passed for the named parameter. It is meant for optional by-reference "out" parameters, like the `&$count` parameter of `str_replace()`: writing a value back to the caller's variable is the only side effect, and it only happens when the caller passes that argument.

The `$value` parameter of a `set` property hook (PHP 8.4+) is always passed by value — PHP does not allow it to be declared by reference. A by-value parameter cannot affect anything outside the hook, so the tag is meaningless here. This mirrors [`pureMethod.parameterPassedNotByRef`](/error-identifiers/pureMethod.parameterPassedNotByRef) for regular methods.

## How to fix it

Remove the tag from the property hook:

```diff-php
 	public int $count = 0 {
-		/**
-		 * @pure-unless-parameter-passed $value
-		 */
 		set (int $value) {
 			$this->count = $value;
 		}
 	}
```
