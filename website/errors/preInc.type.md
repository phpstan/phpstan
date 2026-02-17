---
title: "preInc.type"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(stdClass $obj): void
{
	++$obj;
}
```

## Why is it reported?

The pre-increment operator `++` cannot be used on certain types such as objects (other than `SimpleXMLElement`), arrays, or resources. PHPStan reports this error when the operand type does not support the increment operation.

## How to fix it

Use a supported numeric type, or perform the operation through a method or explicit arithmetic:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(stdClass $obj): void
+function doFoo(int $counter): void
 {
-	++$obj;
+	++$counter;
 }
```
