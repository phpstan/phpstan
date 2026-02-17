---
title: "propertyTag.deprecatedClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewConfig instead */
class OldConfig
{
	public string $value;
}

/**
 * @property OldConfig $config
 */
class Foo
{
	use MagicPropertyTrait;
}
```

## Why is it reported?

A `@property` PHPDoc tag references a class that has been marked as `@deprecated`. Using deprecated classes in PHPDoc type declarations ties the code to types that are scheduled for removal. The deprecation notice typically indicates that a replacement exists.

This rule is provided by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) package.

## How to fix it

Replace the deprecated class with its recommended replacement in the `@property` tag:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @property OldConfig $config
+ * @property NewConfig $config
  */
 class Foo
 {
 	use MagicPropertyTrait;
 }
```

If the deprecated class has no direct replacement, update the `@property` tag to reference the new type that serves the same role.
