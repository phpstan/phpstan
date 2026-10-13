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
		set {
			$this->name = $value;
		}
	}

}
```

## Why is it reported?

The `@pure-unless-parameter-passed` tag marks a function as pure *unless* the named optional parameter is passed. It is meant for by-reference "out" parameters, like the `$count` parameter of `str_replace()`: writing into the caller's variable is a side effect that only happens when the caller passes that argument.

The `$value` parameter of a `set` property hook (PHP 8.4+) is always passed by value; PHP does not allow hook parameters to be passed by reference. Receiving a copy of a value cannot make the hook impure on its own, so the tag does not describe anything that can be verified. This mirrors [`pureMethod.parameterPassedNotByRef`](/error-identifiers/pureMethod.parameterPassedNotByRef) for regular methods.

This identifier exists so that property hooks are covered alongside functions and methods. Current PHPStan versions do not read `@pure-unless-parameter-passed` on property hooks, so the tag in the example above is ignored and this error is not reported in practice. Because the tag can never be meaningful on a hook, it should not be used there.

## How to fix it

Remove the tag from the property hook:

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

If the conditional purity is needed, move the logic into a regular method with an optional by-reference parameter, where `@pure-unless-parameter-passed` is meaningful.
