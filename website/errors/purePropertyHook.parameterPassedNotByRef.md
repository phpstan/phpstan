---
title: "purePropertyHook.parameterPassedNotByRef"
shortDescription: "Property hook is marked @pure-unless-parameter-passed for a parameter that is not passed by reference."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

final class Foo
{

	public string $name {
		/** @pure-unless-parameter-passed $value */
		set(string $value) { // ERROR: Set hook for property Foo::$name is marked @pure-unless-parameter-passed for parameter $value, but $value is not passed by reference.
			$this->name = $value;
		}
	}

}
```

## Why is it reported?

The `@pure-unless-parameter-passed` tag marks a function as pure *unless* the caller passes the named parameter. It is meant for optional by-reference "out" parameters, like the `$count` parameter of `str_replace()`: writing to such a parameter changes the caller's variable, so passing it makes the call impure, while omitting it keeps the call pure.

A parameter passed by value cannot cause a side effect on its own. The `$value` parameter of a `set` property hook (PHP 8.4+) is always passed by value — PHP does not allow by-reference parameters in property hooks — so the tag cannot express anything meaningful here.

This identifier exists for consistency with [`pureFunction.parameterPassedNotByRef`](/error-identifiers/pureFunction.parameterPassedNotByRef) and [`pureMethod.parameterPassedNotByRef`](/error-identifiers/pureMethod.parameterPassedNotByRef), which apply to regular functions and methods where by-reference parameters are permitted.

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
