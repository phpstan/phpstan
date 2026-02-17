---
title: "staticProperty.protected"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Base
{
	protected static int $counter = 0;
}

class Unrelated
{
	public function read(): int
	{
		return Base::$counter;
	}
}
```

## Why is it reported?

The code accesses a protected static property from a class that is not a subclass of the declaring class. Protected properties can only be accessed from within the declaring class or its descendants.

## How to fix it

Access the property from within the class hierarchy, or provide a public accessor method.

```diff-php
 <?php declare(strict_types = 1);

 class Base
 {
 	protected static int $counter = 0;
+
+	public static function getCounter(): int
+	{
+		return static::$counter;
+	}
 }

 class Unrelated
 {
 	public function read(): int
 	{
-		return Base::$counter;
+		return Base::getCounter();
 	}
 }
```
