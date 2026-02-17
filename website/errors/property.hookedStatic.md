---
title: "property.hookedStatic"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public static string $name { // ERROR: Hooked properties cannot be static.
		get => 'hello';
	}
}
```

## Why is it reported?

PHP does not allow property hooks on static properties. Property hooks (`get` and `set`) are designed to work with instance properties and rely on `$this` context. Static properties belong to the class rather than to an instance, so hooks are not supported for them.

## How to fix it

If the property needs hooks, make it an instance property:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	public static string $name {
+	public string $name {
 		get => 'hello';
 	}
 }
```

If the property needs to be static, remove the hooks and use a static method instead:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	public static string $name {
-		get => 'hello';
-	}
+	public static string $name = 'hello';
 }
```
