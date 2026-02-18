---
title: "classConstant.missingNativeType"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Base
{
	public const string NAME = 'base';
}

class Child extends Base
{
	public const NAME = 'child';
}
```

## Why is it reported?

A class constant overrides a parent constant that has a native type declaration (available since PHP 8.3), but the child constant does not declare a native type. When a parent constant has a native type, the overriding constant must also declare a compatible native type to maintain type safety.

In the example above, `Base::NAME` is typed as `string`, but `Child::NAME` does not declare a native type.

## How to fix it

Add the native type to the overriding constant:

```diff-php
 <?php declare(strict_types = 1);

 class Base
 {
 	public const string NAME = 'base';
 }

 class Child extends Base
 {
-	public const NAME = 'child';
+	public const string NAME = 'child';
 }
```
