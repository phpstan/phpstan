---
title: "purePropertyHook.nonOptionalParameterPassed"
shortDescription: "Property hook is marked @pure-unless-parameter-passed for a parameter that is not optional."
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
		set { // ERROR: Set hook for property User::$name is marked @pure-unless-parameter-passed for parameter $value, but $value is not optional, so set hook for property User::$name is never pure.
			$this->name = $value;
		}
	}

}
```

## Why is it reported?

The `@pure-unless-parameter-passed` tag marks a function as pure *unless* the named parameter is passed. Calls that omit the argument are pure, and calls that pass it are impure. This only makes sense for optional by-reference parameters, like the `$count` parameter of `str_replace()`.

A `set` property hook (PHP 8.4+) always receives the assigned value as its `$value` parameter. PHP does not allow this parameter to have a default value, so it is always passed. The condition that would keep the hook pure can never happen, which makes the tag meaningless. The same parameter is also passed by value, so PHPStan also reports [`purePropertyHook.parameterPassedNotByRef`](/error-identifiers/purePropertyHook.parameterPassedNotByRef) for it. This mirrors [`pureMethod.nonOptionalParameterPassed`](/error-identifiers/pureMethod.nonOptionalParameterPassed) for regular methods.

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
