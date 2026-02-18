---
title: "classConstant.phpDocType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	/** @var string */
	const BAZ = 1;
}
```

## Why is it reported?

The `@var` PHPDoc tag on the class constant declares a type that is incompatible with the actual value assigned to the constant. In the example above, the PHPDoc declares the type as `string`, but the constant is assigned the integer value `1`. This mismatch means the PHPDoc type does not accurately describe the constant's value, which can lead to incorrect type inference in code that references the constant.

## How to fix it

Fix the PHPDoc type to match the actual value:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	/** @var string */
+	/** @var int */
 	const BAZ = 1;
 }
```

Or fix the value to match the declared type:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	/** @var string */
-	const BAZ = 1;
+	const BAZ = 'baz';
 }
```
