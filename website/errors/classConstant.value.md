---
title: "classConstant.value"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public const int VALUE = 'hello';
}
```

## Why is it reported?

The value assigned to a class constant does not match the constant's declared type. PHP 8.3 introduced native types for class constants. When a type is declared, the assigned value must be compatible with that type.

This error is also reported when a class overrides a trait constant with a different value, which can lead to inconsistencies.

## How to fix it

Change the value to match the declared type:

```diff-php
 class Foo
 {
-	public const int VALUE = 'hello';
+	public const int VALUE = 42;
 }
```

Or change the type to match the value:

```diff-php
 class Foo
 {
-	public const int VALUE = 'hello';
+	public const string VALUE = 'hello';
 }
```
