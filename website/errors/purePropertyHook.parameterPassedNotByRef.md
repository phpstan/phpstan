---
title: "purePropertyHook.parameterPassedNotByRef"
shortDescription: "Property hook is marked @pure-unless-parameter-passed for a parameter that is not passed by reference."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

final class User
{

	public string $name {
		/**
		 * @pure-unless-parameter-passed $value
		 */
		set { // ERROR: Set hook for property User::$name is marked @pure-unless-parameter-passed for parameter $value, but $value is not passed by reference.
			$this->name = $value;
		}
	}

}
```

## Why is it reported?

The `@pure-unless-parameter-passed` tag marks a function as pure *unless* the named parameter is passed. It is meant for optional by-reference "out" parameters, like the `$count` parameter of `str_replace()`: when the caller passes a variable, the function writes to it, which is a side effect visible to the caller.

The `$value` parameter of a `set` property hook (PHP 8.4+) is always passed by value. PHP does not allow it to be declared by reference. Passing a value can never cause a side effect on its own, so the tag cannot describe the hook correctly. The parameter is also never optional, so PHPStan also reports [`purePropertyHook.nonOptionalParameterPassed`](/error-identifiers/purePropertyHook.nonOptionalParameterPassed) for it. This mirrors [`pureMethod.parameterPassedNotByRef`](/error-identifiers/pureMethod.parameterPassedNotByRef) for regular methods.

## How to fix it

Remove the tag. If the hook writes to the object, it has side effects, so mark it `@phpstan-impure` or leave it without a purity tag:

```diff-php
 	public string $name {
-		/**
-		 * @pure-unless-parameter-passed $value
-		 */
 		set {
 			$this->name = $value;
 		}
 	}
```
