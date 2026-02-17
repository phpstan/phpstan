---
title: "propertyTag.deprecatedEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewStatus instead */
enum OldStatus: string
{
	case Active = 'active';
	case Inactive = 'inactive';
}

/**
 * @property OldStatus $status
 */
class Foo
{
	use MagicPropertyTrait;
}
```

## Why is it reported?

A `@property` PHPDoc tag references an enum that has been marked as `@deprecated`. Using deprecated enums in PHPDoc type declarations ties the code to types that are scheduled for removal. The deprecation notice typically indicates that a replacement exists.

This rule is provided by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) package.

## How to fix it

Replace the deprecated enum with its recommended replacement in the `@property` tag:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @property OldStatus $status
+ * @property NewStatus $status
  */
 class Foo
 {
 	use MagicPropertyTrait;
 }
```
