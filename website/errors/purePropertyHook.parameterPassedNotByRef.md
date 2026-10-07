---
title: "purePropertyHook.parameterPassedNotByRef"
shortDescription: "Property hook is marked @pure-unless-parameter-passed for a parameter that is not passed by reference."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

final class Article
{

	public string $title = '' {
		/**
		 * @pure-unless-parameter-passed $value
		 */
		set(string $value) {
			$this->title = trim($value);
		}
	}

}
```

## Why is it reported?

The `@pure-unless-parameter-passed` tag marks a function or method as pure *unless* the named parameter is passed by the caller. It is meant for optional by-reference out parameters, like the `$count` parameter of PHP's `str_replace()`: writing to the caller's variable is the only side effect, and it happens only when the argument is passed.

The `$value` parameter of a `set` property hook (PHP 8.4+) is always passed by value and cannot be declared by reference. Passing it cannot change anything outside the hook, so the tag says nothing that can be checked. This mirrors [`pureMethod.parameterPassedNotByRef`](/error-identifiers/pureMethod.parameterPassedNotByRef) for regular methods.

## How to fix it

Remove the tag from the hook:

```diff-php
 	public string $title = '' {
-		/**
-		 * @pure-unless-parameter-passed $value
-		 */
 		set(string $value) {
 			$this->title = trim($value);
 		}
 	}
```
