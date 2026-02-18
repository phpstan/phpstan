---
title: "property.deprecatedTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewHelper instead */
class Helper
{
	public int $value = 0;
}

class Service
{
	/** @var Helper */
	public $helper;
}
```

## Why is it reported?

The property's native type declaration references a deprecated trait (or class that is actually a trait). Continuing to use deprecated types couples code to APIs that are planned for removal.

This also triggers when accessing a property of an object whose declaring class is deprecated.

## How to fix it

Replace the deprecated type with its recommended replacement:

```diff-php
 class Service
 {
-	/** @var Helper */
-	public $helper;
+	/** @var NewHelper */
+	public $helper;
 }
```
