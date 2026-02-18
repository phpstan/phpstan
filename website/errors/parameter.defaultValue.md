---
title: "parameter.defaultValue"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function greet(string $name = null): string
{
	return 'Hello, ' . $name;
}
```

## Why is it reported?

The default value of a parameter is incompatible with the parameter's declared type. PHP would accept this at the function declaration, but it indicates a type mismatch that can lead to unexpected behavior.

In the example above, the parameter `$name` is declared as `string`, but its default value is `null`, which is not a `string`.

## How to fix it

Make the parameter type nullable to match the default value:

```diff-php
-function greet(string $name = null): string
+function greet(?string $name = null): string
 {
 	return 'Hello, ' . ($name ?? 'World');
 }
```

Or change the default value to match the declared type:

```diff-php
-function greet(string $name = null): string
+function greet(string $name = 'World'): string
 {
 	return 'Hello, ' . $name;
 }
```
