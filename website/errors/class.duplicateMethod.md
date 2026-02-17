---
title: "class.duplicateMethod"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public function doSomething(): void
	{
	}

	public function doSomething(): void
	{
	}
}
```

## Why is it reported?

A class declares the same method more than once. PHP does not allow two methods with the same name in a single class. This is a fatal error.

In the example above, the method `doSomething()` is declared twice in the class `Foo`.

## How to fix it

Remove the duplicate method declaration, keeping only one:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	public function doSomething(): void
 	{
 	}
-
-	public function doSomething(): void
-	{
-	}
 }
```

If the duplicate methods were intended to have different behavior, rename one of them:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	public function doSomething(): void
 	{
 	}

-	public function doSomething(): void
+	public function doSomethingElse(): void
 	{
 	}
 }
```
