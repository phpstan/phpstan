---
title: "property.defaultValue"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public int $count = 'hello';
}
```

## Why is it reported?

The default value assigned to a property does not match the property's declared native type. In the example above, the property `$count` is declared as `int`, but its default value is the string `'hello'`. This would cause a `TypeError` at runtime.

PHPStan checks that the default value is compatible with the property's type declaration so that the class can be instantiated without errors.

## How to fix it

Change the default value to match the property's type:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	public int $count = 'hello';
+	public int $count = 0;
 }
```

Or change the property type to match the default value:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	public int $count = 'hello';
+	public string $count = 'hello';
 }
```
