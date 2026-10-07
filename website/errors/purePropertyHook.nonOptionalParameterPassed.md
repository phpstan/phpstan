---
title: "purePropertyHook.nonOptionalParameterPassed"
shortDescription: "Property hook is marked @pure-unless-parameter-passed for a parameter that is not optional."
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

The `@pure-unless-parameter-passed` tag marks a function or method as pure *unless* the named optional by-reference parameter is passed by the caller, like the `$count` parameter of PHP's `str_replace()`.

A `set` property hook (PHP 8.4+) always receives exactly one argument, `$value`, which is assigned whenever the property is written. It cannot have a default value, so it is always passed. The case where the hook would be pure can never happen, so the tag does not make sense on a property hook. This mirrors [`pureMethod.nonOptionalParameterPassed`](/error-identifiers/pureMethod.nonOptionalParameterPassed) for regular methods.

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
