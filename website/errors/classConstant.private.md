---
title: "classConstant.private"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	private const SECRET = 'hidden';
}

echo Foo::SECRET;
```

## Why is it reported?

The code is accessing a class constant that has `private` visibility from outside the class where it is declared. Private constants can only be accessed from within the same class.

## How to fix it

If you need to access the value from outside the class, change the constant's visibility:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	private const SECRET = 'hidden';
+	public const SECRET = 'hidden';
 }

 echo Foo::SECRET;
```

Or provide a public method to access the value:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	private const SECRET = 'hidden';

+	public static function getSecret(): string
+	{
+		return self::SECRET;
+	}
 }

-echo Foo::SECRET;
+echo Foo::getSecret();
```
