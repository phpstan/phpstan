---
title: "purePropertyHook.nonOptionalParameterPassed"
shortDescription: "Property hook is marked @pure-unless-parameter-passed for a parameter that is not optional, so it is never pure."
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
		set {
			$this->name = $value;
		}
	}
}
```

## Why is it reported?

The `@pure-unless-parameter-passed` tag marks a function as pure *unless* an argument is passed for the named optional by-reference parameter, like the `$count` parameter of `str_replace()`.

The `$value` parameter of a `set` property hook (PHP 8.4+) always receives the assigned value — it can never be omitted. The condition under which the hook would be pure can never be met, so the tag has no meaning here. This mirrors [`pureMethod.nonOptionalParameterPassed`](/error-identifiers/pureMethod.nonOptionalParameterPassed) for regular methods.

The same tag also triggers [`purePropertyHook.parameterPassedNotByRef`](/error-identifiers/purePropertyHook.parameterPassedNotByRef), because `$value` is passed by value.

## How to fix it

Remove the tag. A property hook cannot have an optional out-parameter, so `@pure-unless-parameter-passed` never applies to it:

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
