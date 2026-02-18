---
title: "trait.disallowedSubtype"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @phpstan-sealed AllowedUser
 */
interface UserProvider
{
}

trait UserTrait
{
}

class AllowedUser implements UserProvider
{
	use UserTrait;
}

class DisallowedUser implements UserProvider
{
	use UserTrait;
}
```

## Why is it reported?

A trait is used in a class that is not allowed to be a subtype of a sealed parent. The parent class or interface restricts which types can extend or implement it using the `@phpstan-sealed` PHPDoc tag. The class using this trait is not listed among the allowed subtypes.

Note: the `trait.` prefix in the identifier indicates that the disallowed subtype is a trait definition. This follows the same logic as `class.disallowedSubtype` but applies when a trait body is checked.

## How to fix it

Add the class to the list of allowed subtypes:

```diff-php
 /**
- * @phpstan-sealed AllowedUser
+ * @phpstan-sealed AllowedUser|DisallowedUser
  */
 interface UserProvider
 {
 }
```

Or remove the sealed parent from the class declaration:

```diff-php
-class DisallowedUser implements UserProvider
+class DisallowedUser
 {
 	use UserTrait;
 }
```
