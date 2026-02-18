---
title: "property.nonObject"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(string $name): void
{
	echo $name->length; // ERROR: Cannot access property $length on string.
}
```

## Why is it reported?

The code attempts to access a property on a value that is not an object. Properties can only be accessed on objects in PHP. Accessing a property on a scalar type (string, int, float, bool), null, or an array will result in a warning or error at runtime.

## How to fix it

Fix the code so that the property is accessed on an object, or use the appropriate function or method for the value type:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(string $name): void
 {
-	echo $name->length;
+	echo strlen($name);
 }
```

If the variable can be null, narrow the type before accessing the property:

```diff-php
 <?php declare(strict_types = 1);

-function getName(?User $user): string
+function getName(?User $user): string
 {
+	if ($user === null) {
+		return 'Unknown';
+	}
+
 	return $user->name;
 }
```
