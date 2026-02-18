---
title: "constructor.returnType"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public function __construct(): void
	{
	}
}
```

## Why is it reported?

Constructors in PHP must not declare a return type. The `__construct` method is a special method that initialises a new object instance and is not expected to return a value. While PHP does not enforce this at the syntax level in all versions, specifying a return type on a constructor is semantically incorrect and may cause confusion. PHP itself will emit a deprecation notice for constructors with return types in future versions.

## How to fix it

Remove the return type from the constructor:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	public function __construct(): void
+	public function __construct()
 	{
 	}
 }
```
