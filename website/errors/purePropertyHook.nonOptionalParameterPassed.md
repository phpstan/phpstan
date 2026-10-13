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
		set {
			$this->name = $value;
		}
	}

}
```

## Why is it reported?

The `@pure-unless-parameter-passed` tag marks a function as pure *unless* the named optional parameter is passed by the caller. Calls that omit the argument are treated as pure, while calls that pass it are treated as impure.

The `$value` parameter of a `set` property hook (PHP 8.4+) is always supplied by the assignment that triggers the hook, and PHP does not allow it to have a default value. The condition under which the hook would be pure can never happen, so the tag has no effect. This mirrors [`pureMethod.nonOptionalParameterPassed`](/error-identifiers/pureMethod.nonOptionalParameterPassed) for regular methods.

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
