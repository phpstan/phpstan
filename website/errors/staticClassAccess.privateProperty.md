---
title: "staticClassAccess.privateProperty"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Counter
{
	private static int $count = 0;

	public function increment(): void
	{
		static::$count++;
	}
}
```

## Why is it reported?

Accessing a private static property through `static::` instead of `self::` is unsafe. The `static::` keyword uses late static binding, which resolves to the class that actually calls the method at runtime. If a child class calls this method, `static::` would resolve to the child class, but private properties are not inherited and belong only to the declaring class. This can lead to unexpected behavior.

## How to fix it

Use `self::` instead of `static::` to access private static properties, since they belong exclusively to the declaring class.

```diff-php
 <?php declare(strict_types = 1);

 class Counter
 {
 	private static int $count = 0;

 	public function increment(): void
 	{
-		static::$count++;
+		self::$count++;
 	}
 }
```
