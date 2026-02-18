---
title: "property.static"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Base
{
	public int $count = 0;
}

class Child extends Base
{
	public static int $count = 0;
}
```

## Why is it reported?

The child class declares a static property that overrides a non-static (instance) property in the parent class. A property cannot change between static and non-static when overriding, as they represent fundamentally different kinds of storage -- static properties are shared across all instances while instance properties are unique to each object.

This is a PHP language-level restriction.

## How to fix it

Match the parent property's static/non-static declaration:

```diff-php
 class Child extends Base
 {
-	public static int $count = 0;
+	public int $count = 0;
 }
```

If a separate static counter is needed, use a different property name:

```diff-php
 class Child extends Base
 {
-	public static int $count = 0;
+	public static int $totalCount = 0;
 }
```
