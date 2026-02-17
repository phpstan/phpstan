---
title: "instanceof.alwaysFalse"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Dog {}
class Cat {}

function doFoo(Dog $dog): void
{
	if ($dog instanceof Cat) { // ERROR: Instanceof between Dog and Cat will always evaluate to false.
		echo 'This is a cat';
	}
}
```

## Why is it reported?

The `instanceof` check will always evaluate to `false` because the type of the checked expression can never be an instance of the specified class. In this example, the parameter `$dog` is typed as `Dog`, which has no relationship to `Cat` (neither extends nor implements it), so the check is always false. This usually indicates a logic error, a wrong variable being checked, or a wrong class name.

## How to fix it

Check against the correct class:

```diff-php
 <?php declare(strict_types = 1);

 class Dog {}
 class Cat {}

 function doFoo(Dog $dog): void
 {
-	if ($dog instanceof Cat) {
-		echo 'This is a cat';
+	if ($dog instanceof Dog) {
+		echo 'This is a dog';
 	}
 }
```

Or widen the parameter type if the function should accept multiple types:

```diff-php
 <?php declare(strict_types = 1);

 class Dog {}
 class Cat {}

-function doFoo(Dog $dog): void
+function doFoo(Dog|Cat $animal): void
 {
-	if ($dog instanceof Cat) {
+	if ($animal instanceof Cat) {
 		echo 'This is a cat';
 	}
 }
```
