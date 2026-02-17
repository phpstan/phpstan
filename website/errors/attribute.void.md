---
title: "attribute.void"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

#[\Attribute]
class MyAttribute
{
	public function __construct()
	{
	}
}

// The result of a void-returning call used in an attribute context.
```

## Why is it reported?

The result of a call that returns `void` is being used in an attribute context. A `void` return type indicates that no meaningful value is returned, so using the result is a logic error.

## How to fix it

Do not use the return value of a void call. Ensure the attribute class constructor returns appropriate values or restructure your code so the void return value is not consumed.
