---
title: "property.missingNativeType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Base
{

	public int $value = 0;

}

class Child extends Base
{

	/** @var int */
	public $value = 0;

}
```

## Why is it reported?

The parent class declares a property with a native type, but the overriding property in the child class does not have a native type declaration. When a parent property has a native type, the child property must also declare the same native type to maintain type safety. Omitting the native type in the child class creates an inconsistency that PHP will reject at runtime.

## How to fix it

Add the matching native type declaration to the overriding property:

```diff-php
 <?php declare(strict_types = 1);

 class Base
 {

 	public int $value = 0;

 }

 class Child extends Base
 {

-	/** @var int */
-	public $value = 0;
+	public int $value = 0;

 }
```
