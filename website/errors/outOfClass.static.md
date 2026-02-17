---
title: "outOfClass.static"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$value = static::FOO;
```

## Why is it reported?

The `static` keyword is being used to access a class constant (or in a class-name context) outside of a class scope. The `static` keyword in this context refers to the class the method was called on (late static binding), but it is only meaningful inside a class method. Using it outside of a class results in a fatal error at runtime.

## How to fix it

Use the actual class name instead of `static`, or move the code inside a class method.

```diff-php
 <?php declare(strict_types = 1);

-$value = static::FOO;
+$value = MyClass::FOO;
```

Or move the code inside a class:

```diff-php
 <?php declare(strict_types = 1);

 class MyClass
 {
 	public const FOO = 'bar';

 	public function getValue(): string
 	{
 		return static::FOO;
 	}
 }
```
