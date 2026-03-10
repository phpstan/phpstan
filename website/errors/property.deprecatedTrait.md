---
title: "property.deprecatedTrait"
shortDescription: "Property type references a deprecated trait."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewHelper instead */
trait OldHelper
{
}

class Foo
{
	/** @var OldHelper */
	public $helper;
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

A property has a PHPDoc type that references a trait marked as `@deprecated`. Deprecated traits are planned for removal in a future version, and property types should not rely on them.

## How to fix it

Replace the deprecated trait with a non-deprecated type:

```diff-php
 class Foo
 {
-	/** @var OldHelper */
+	/** @var NewHelper */
 	public $helper;
 }
```
