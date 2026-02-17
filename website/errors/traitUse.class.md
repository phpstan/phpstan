---
title: "traitUse.class"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Helper
{
	public function help(): void
	{
	}
}

class MyClass
{
	use Helper;
}
```

## Why is it reported?

The `use` statement inside a class body is attempting to use a class as if it were a trait. The `use` keyword in a class body is reserved exclusively for traits. Classes cannot be used with the `use` statement. This code will result in a fatal error at runtime.

## How to fix it

If the intent is to reuse functionality from the class, extend it instead:

```diff-php
 <?php declare(strict_types = 1);

-class MyClass
+class MyClass extends Helper
 {
-	use Helper;
 }
```

If inheritance is not appropriate, use composition:

```diff-php
 <?php declare(strict_types = 1);

 class MyClass
 {
-	use Helper;
+	private Helper $helper;
+
+	public function __construct()
+	{
+		$this->helper = new Helper();
+	}
 }
```

Or convert `Helper` into a trait if code reuse via traits is the intended approach:

```diff-php
 <?php declare(strict_types = 1);

-class Helper
+trait Helper
 {
 	public function help(): void
 	{
 	}
 }
```
