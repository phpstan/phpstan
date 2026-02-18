---
title: "property.staticAccess"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public int $value = 42;
}

echo Foo::$value;
```

## Why is it reported?

The code accesses an instance property using static syntax (`ClassName::$property`). The property `$value` is declared as an instance property (non-static), so it belongs to individual objects, not to the class itself. Accessing it statically is incorrect and will cause a runtime error.

## How to fix it

Access the property on an instance of the class:

```diff-php
-echo Foo::$value;
+$foo = new Foo();
+echo $foo->value;
```

If the property should be shared across all instances, declare it as `static`:

```diff-php
 class Foo
 {
-	public int $value = 42;
+	public static int $value = 42;
 }
```
