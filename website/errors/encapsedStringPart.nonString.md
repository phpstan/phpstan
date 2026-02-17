---
title: "encapsedStringPart.nonString"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function greet(\stdClass $obj): string
{
    return "Hello, $obj!";
}
```

## Why is it reported?

An interpolated (double-quoted) string contains an expression that cannot be cast to `string`. In PHP, when a variable is embedded inside a double-quoted string, PHP attempts to convert it to a string. Objects that do not implement the `__toString()` method cannot be converted, resulting in a fatal error at runtime.

## How to fix it

Access a string property of the object instead of interpolating the object directly:

```diff-php
 <?php declare(strict_types = 1);

 function greet(\stdClass $obj): string
 {
-    return "Hello, $obj!";
+    return "Hello, {$obj->name}!";
 }
```

Or implement the `__toString()` method on the class:

```diff-php
 <?php declare(strict_types = 1);

-function greet(\stdClass $obj): string
+class User
+{
+    public function __construct(public string $name) {}
+
+    public function __toString(): string
+    {
+        return $this->name;
+    }
+}
+
+function greet(User $user): string
 {
-    return "Hello, $obj!";
+    return "Hello, $user!";
 }
```
