---
title: "enum.implementsDeprecatedInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewInterface instead */
interface OldInterface
{
	public function doSomething(): void;
}

enum Status: string implements OldInterface
{
	case Active = 'active';
	case Inactive = 'inactive';

	public function doSomething(): void
	{
	}
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

An enum implements an interface that has been marked as `@deprecated`. Implementing deprecated interfaces ties the enum to a contract that is planned for removal.

In the example above, enum `Status` implements `OldInterface`, which is deprecated.

## How to fix it

Replace the deprecated interface with its recommended replacement:

```diff-php
 <?php declare(strict_types = 1);

-enum Status: string implements OldInterface
+enum Status: string implements NewInterface
 {
 	case Active = 'active';
 	case Inactive = 'inactive';

 	public function doSomething(): void
 	{
 	}
 }
```
