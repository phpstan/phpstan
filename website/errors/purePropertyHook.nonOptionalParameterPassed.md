---
title: "purePropertyHook.nonOptionalParameterPassed"
shortDescription: "Property hook is marked @pure-unless-parameter-passed for a parameter that is not optional."
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

The `@pure-unless-parameter-passed` tag marks a function as pure *unless* an argument is passed for the named optional by-reference parameter, like the `&$count` parameter of `str_replace()`. When the caller omits the argument, the call is pure.

The `$value` parameter of a `set` property hook (PHP 8.4+) is never optional — PHP does not allow it to have a default value, and every assignment to the property passes the new value. The condition that would keep the hook pure can never be met, so the tag is meaningless on a property hook. This mirrors [`pureMethod.nonOptionalParameterPassed`](/error-identifiers/pureMethod.nonOptionalParameterPassed) for regular methods.

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
