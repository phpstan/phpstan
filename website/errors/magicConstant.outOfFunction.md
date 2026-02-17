---
title: "magicConstant.outOfFunction"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

echo __FUNCTION__;
echo __METHOD__;
```

## Why is it reported?

The magic constants `__FUNCTION__` and `__METHOD__` are used outside of a function or method. In this context, they always resolve to an empty string `''`, which is almost certainly not the intended behavior.

PHP defines these magic constants to return the name of the current function or method. When used at the top level of a script or in a class property initializer (outside a method), there is no function context, so the value is always empty.

## How to fix it

Move the usage inside a function or method where the magic constant has a meaningful value:

```diff-php
-echo __FUNCTION__;

+function myFunction(): void
+{
+    echo __FUNCTION__; // outputs "myFunction"
+}
```

If you need the file or line information at the top level, use an appropriate magic constant instead:

```diff-php
-echo __FUNCTION__;
+echo __FILE__;
```
