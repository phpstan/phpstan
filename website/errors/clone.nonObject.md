---
title: "clone.nonObject"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$value = 'hello';

$cloned = clone $value;
```

## Why is it reported?

The `clone` keyword is applied to a value that is not an object. In PHP, only objects can be cloned. Attempting to clone a non-object value will result in a `TypeError` at runtime.

In the example above, a `string` is being cloned, which is not valid.

## How to fix it

Ensure the value being cloned is an object:

```diff-php
 <?php declare(strict_types = 1);

-$value = 'hello';
+$value = new \stdClass();

 $cloned = clone $value;
```

If the variable can hold both object and non-object types, narrow the type before cloning:

```diff-php
 <?php declare(strict_types = 1);

 function cloneIfObject(mixed $value): mixed
 {
+	if (is_object($value)) {
+		return clone $value;
+	}
+
-	return clone $value;
+	return $value;
 }
```
