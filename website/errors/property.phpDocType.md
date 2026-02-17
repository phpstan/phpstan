---
title: "property.phpDocType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	/** @var string */
	public int $value;
}
```

## Why is it reported?

The PHPDoc type for a property is incompatible with its native type declaration. In the example above, the `@var` tag says the property is `string`, but the native type is `int`. These types are incompatible.

## How to fix it

Align the PHPDoc type with the native type:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	/** @var string */
+	/** @var int */
 	public int $value;
 }
```

Or update the native type to match the intended PHPDoc type.
