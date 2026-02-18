---
title: "property.extraNativeType"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Base
{
	public $name;
}

class Child extends Base
{
	public int $name;
}
```

## Why is it reported?

The child class declares a native type on a property that has no native type in the parent class. When a parent property is untyped, the child property must also remain untyped. Adding a native type in the child would change the property's type contract, which PHP does not allow.

## How to fix it

Remove the native type from the child property to match the parent:

```diff-php
 class Child extends Base
 {
-	public int $name;
+	public $name;
 }
```

Alternatively, add the native type to the parent property first, then the child can match it:

```diff-php
 class Base
 {
-	public $name;
+	public int $name;
 }
```
