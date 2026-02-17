---
title: "staticProperty.notFound"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public static int $count = 0;
}

function doFoo(): void
{
	echo Foo::$name;
}
```

## Why is it reported?

The code accesses a static property that does not exist on the specified class. In PHP, accessing an undefined static property results in a fatal error at runtime. PHPStan reports this to catch the error before the code is executed.

## How to fix it

Fix the property name if it was a typo:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): void
 {
-	echo Foo::$name;
+	echo Foo::$count;
 }
```

Or add the missing static property to the class:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	public static int $count = 0;
+
+	public static string $name = '';
 }
```

If the class uses `__get` via a magic property pattern, declare the property using a `@property` PHPDoc tag on the class:

```diff-php
 <?php declare(strict_types = 1);

+/**
+ * @property string $name
+ */
 class Foo
 {
 	public static int $count = 0;
 }
```
