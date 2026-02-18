---
title: "property.nonStatic"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Base
{
	public static string $name = 'base';
}

class Child extends Base
{
	public string $name = 'child';
}
```

## Why is it reported?

A non-static property in a child class overrides a static property in the parent class. PHP does not allow changing the static/non-static nature of a property during inheritance. This causes a fatal error at runtime.

## How to fix it

Match the parent property's static modifier:

```diff-php
 class Child extends Base
 {
-	public string $name = 'child';
+	public static string $name = 'child';
 }
```

Or redesign the class hierarchy so the property does not conflict:

```diff-php
 class Child extends Base
 {
-	public string $name = 'child';
+	public string $childName = 'child';
 }
```
