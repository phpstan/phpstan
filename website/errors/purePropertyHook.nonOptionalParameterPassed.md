---
title: "purePropertyHook.nonOptionalParameterPassed"
shortDescription: "Property hook is marked @pure-unless-parameter-passed for a required parameter, so it can never be pure."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

final class Foo
{

	public string $name {
		/** @pure-unless-parameter-passed $value */
		set(string $value) { // ERROR: Set hook for property Foo::$name is marked @pure-unless-parameter-passed for parameter $value, but $value is not optional, so set hook for property Foo::$name is never pure.
			$this->name = $value;
		}
	}

}
```

## Why is it reported?

The `@pure-unless-parameter-passed` tag marks a function as pure *unless* the caller passes the named parameter. It only makes sense for optional parameters that callers can choose to omit.

The `$value` parameter of a `set` property hook (PHP 8.4+) is always supplied by PHP when the property is assigned, and PHP does not allow it to have a default value. The condition under which the hook would be pure can never be met, so the tag has no effect.

The same tag on this parameter is also reported as [`purePropertyHook.parameterPassedNotByRef`](/error-identifiers/purePropertyHook.parameterPassedNotByRef), because property hook parameters cannot be passed by reference either. This identifier exists for consistency with [`pureFunction.nonOptionalParameterPassed`](/error-identifiers/pureFunction.nonOptionalParameterPassed) and [`pureMethod.nonOptionalParameterPassed`](/error-identifiers/pureMethod.nonOptionalParameterPassed).

## How to fix it

Remove the tag from the property hook:

```diff-php
 	public string $name {
-		/** @pure-unless-parameter-passed $value */
 		set(string $value) {
 			$this->name = $value;
 		}
 	}
```
