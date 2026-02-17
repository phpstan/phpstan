---
title: "enum.disallowedSubtype"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @phpstan-sealed(AllowedEnum)
 */
interface Permission
{
}

enum AllowedEnum: string implements Permission
{
	case Read = 'read';
}

enum DisallowedEnum: string implements Permission
{
	case Write = 'write';
}
```

## Why is it reported?

The parent class or interface restricts which types are allowed to extend or implement it using the `@phpstan-sealed` PHPDoc tag (or the `AllowedSubTypes` interface). The enum in question is not listed among the allowed subtypes.

In the example above, `Permission` only allows `AllowedEnum` as a subtype. `DisallowedEnum` implements `Permission` but is not in the allowed list, so it is reported.

This mechanism enforces closed type hierarchies, ensuring that only a known set of types can implement a given interface.

## How to fix it

Add the enum to the list of allowed subtypes:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @phpstan-sealed(AllowedEnum)
+ * @phpstan-sealed(AllowedEnum, DisallowedEnum)
  */
 interface Permission
 {
 }
```

Or remove the interface implementation if the enum should not be part of this type hierarchy:

```diff-php
 <?php declare(strict_types = 1);

-enum DisallowedEnum: string implements Permission
+enum DisallowedEnum: string
 {
 	case Write = 'write';
 }
```
