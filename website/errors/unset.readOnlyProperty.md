---
title: "unset.readOnlyProperty"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class User
{
	public function __construct(
		public readonly string $name,
	)
	{
	}
}

function doFoo(User $user): void
{
	unset($user->name);
}
```

## Why is it reported?

The `unset()` call targets a property that is declared as `readonly`. PHP does not allow unsetting readonly properties outside of the constructor scope. Attempting to do so results in a fatal error at runtime.

This error is also reported for properties marked with `@readonly` or `@immutable` PHPDoc tags.

## How to fix it

Remove the `unset()` call and use a different approach to handle the absence of the value. If the property needs to be nullable, change the type and assign `null` instead:

```diff-php
 <?php declare(strict_types = 1);

 class User
 {
 	public function __construct(
-		public readonly string $name,
+		public readonly ?string $name,
 	)
 	{
 	}
 }

-function doFoo(User $user): void
+function createUser(?string $name): User
 {
-	unset($user->name);
+	return new User($name);
 }
```

If the property should be mutable, remove the `readonly` modifier:

```diff-php
 <?php declare(strict_types = 1);

 class User
 {
 	public function __construct(
-		public readonly string $name,
+		public string $name,
 	)
 	{
 	}
 }
```
