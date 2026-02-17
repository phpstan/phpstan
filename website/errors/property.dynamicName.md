---
title: "property.dynamicName"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class User
{
	public string $name = '';
	public string $email = '';
}

function getValue(User $user, string $field): mixed
{
	return $user->$field;
}
```

## Why is it reported?

The code accesses an object property using a variable name (`$object->$variable`) instead of a static identifier (`$object->name`). Variable property access makes the code harder to analyse statically because PHPStan cannot determine which property is being accessed. It also makes the code harder to refactor and more error-prone, since there is no compile-time check that the property exists.

This rule is provided by the [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules) package.

## How to fix it

Replace the variable property access with explicit property access:

```diff-php
 <?php declare(strict_types = 1);

-function getValue(User $user, string $field): mixed
+function getValue(User $user, string $field): string
 {
-	return $user->$field;
+	return match ($field) {
+		'name' => $user->name,
+		'email' => $user->email,
+		default => throw new \InvalidArgumentException("Unknown field: $field"),
+	};
 }
```

Alternatively, use a getter method or implement a controlled access pattern that does not rely on dynamic property names.
