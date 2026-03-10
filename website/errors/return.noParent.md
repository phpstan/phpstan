---
title: "return.noParent"
shortDescription: "Return type uses parent keyword but there is no parent class."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public function create(): parent
	{
	}
}
```

## Why is it reported?

The `parent` keyword in PHP refers to the parent class of the current class. It can only be used inside a class that extends another class. When `parent` is used as a return type in a class that does not extend any other class, it has no meaning because there is no parent class to refer to.

## How to fix it

Replace `parent` with the actual class name that was intended:

```diff-php
 class Foo
 {
-	public function create(): parent
+	public function create(): self
 	{
 	}
 }
```

If the class should have a parent, add the `extends` clause:

```diff-php
-class Foo
+class Foo extends BaseClass
 {
 	public function create(): parent
 	{
+		return new BaseClass();
 	}
 }
```
