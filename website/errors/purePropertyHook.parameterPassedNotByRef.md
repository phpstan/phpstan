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

The `@pure-unless-parameter-passed` tag marks a function as pure *unless* an argument is passed for the named parameter. It is meant for optional by-reference out-parameters: writing into the caller's variable through such a parameter is the side effect that makes the call impure.

The `$value` parameter of a `set` property hook (PHP 8.4+) is passed by value — the hook only receives a copy of the assigned value and cannot write back into the caller's variable. Passing it cannot produce a side effect on its own, so the tag has no meaning here. This mirrors [`pureMethod.parameterPassedNotByRef`](/error-identifiers/pureMethod.parameterPassedNotByRef) for regular methods.

The same tag also triggers [`purePropertyHook.nonOptionalParameterPassed`](/error-identifiers/purePropertyHook.nonOptionalParameterPassed), because `$value` is always passed.

## How to fix it

Remove the tag. Purity of a property hook can be declared with `@phpstan-pure` or `@phpstan-impure` instead:

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
