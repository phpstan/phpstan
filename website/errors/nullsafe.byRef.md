---
title: "nullsafe.byRef"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public string $value = 'hello';
}

function doFoo(?Foo $foo): void
{
	$ref =& $foo?->value;
}
```

## Why is it reported?

The nullsafe operator (`?->`) cannot be used in a context that requires a reference. The nullsafe operator may return `null` when the left side is `null`, and `null` is not a variable that can be referenced. This applies to assignment by reference (`=&`), return by reference, and arrow function return by reference.

## How to fix it

Check for `null` explicitly instead of using the nullsafe operator:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(?Foo $foo): void
 {
-	$ref =& $foo?->value;
+	if ($foo !== null) {
+		$ref =& $foo->value;
+	}
 }
```
