---
title: "cast.string"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$value = (string) new stdClass();
```

## Why is it reported?

The expression being cast to `string` is of a type that cannot be converted to a string. PHP does not support casting certain types to `string` unless the class implements the `__toString()` method. Attempting to cast an object without `__toString()` to `string` will result in a fatal error at runtime.

In the example above, `stdClass` does not implement `__toString()`, so it cannot be cast to `string`.

## How to fix it

Implement the `__toString()` method on the class:

```diff-php
 <?php declare(strict_types = 1);

-$value = (string) new stdClass();
+class MyClass
+{
+    public function __toString(): string
+    {
+        return 'my value';
+    }
+}
+
+$value = (string) new MyClass();
```

Or extract the string value from the object explicitly:

```diff-php
 <?php declare(strict_types = 1);

-$value = (string) new stdClass();
+$obj = new stdClass();
+$obj->name = 'hello';
+$value = $obj->name;
```
