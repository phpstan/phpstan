---
title: "parameter.noParent"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public function doFoo(parent $value): void
	{
	}
}
```

## Why is it reported?

The `parent` type is used in a parameter declaration, but the class does not have a parent class. The `parent` keyword refers to the parent class in the inheritance hierarchy, and it can only be used in classes that extend another class. Using `parent` in a class that does not extend anything causes a fatal error at runtime.

## How to fix it

If the class should have a parent, add the `extends` clause:

```diff-php
-class Foo
+class Foo extends Bar
 {
 	public function doFoo(parent $value): void
 	{
 	}
 }
```

If `parent` was used by mistake, replace it with the intended type:

```diff-php
 class Foo
 {
-	public function doFoo(parent $value): void
+	public function doFoo(self $value): void
 	{
 	}
 }
```
