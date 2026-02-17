---
title: "property.deprecated"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class User
{
	/**
	 * @deprecated Use getName() instead
	 */
	public string $name;

	public function getName(): string
	{
		return $this->name;
	}
}

function greet(User $user): string
{
	return 'Hello, ' . $user->name; // ERROR: Access to deprecated property $name of class User: Use getName() instead
}
```

## Why is it reported?

The code accesses a property that has been marked as `@deprecated` in its PHPDoc. Deprecated properties are scheduled for removal in a future version and should no longer be used. The deprecation notice typically suggests an alternative approach.

This rule is provided by the [phpstan/phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) package.

## How to fix it

Replace the deprecated property access with the suggested alternative:

```diff-php
 <?php declare(strict_types = 1);

 function greet(User $user): string
 {
-	return 'Hello, ' . $user->name;
+	return 'Hello, ' . $user->getName();
 }
```
