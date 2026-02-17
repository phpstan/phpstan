---
title: "class.extendsFinal"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

final class ParentClass
{

}

class ChildClass extends ParentClass // reported
{

}
```

## Why is it reported?

A class declared as `final` cannot be extended. The `final` keyword explicitly prevents inheritance. Attempting to extend a final class results in a fatal error at runtime.

## How to fix it

Either remove the `final` keyword from the parent class if inheritance is intended, or stop extending it and use composition instead.

Using composition:

```diff-php
-class ChildClass extends ParentClass
+class ChildClass
 {
+    public function __construct(
+        private ParentClass $parent,
+    ) {
+    }
 }
```

Or, if you control the parent class and inheritance is appropriate:

```diff-php
-final class ParentClass
+class ParentClass
 {

 }
```
