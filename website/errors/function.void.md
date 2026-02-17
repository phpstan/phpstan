---
title: "function.void"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doSomething(): void
{
    echo 'done';
}

$result = doSomething();
```

## Why is it reported?

The return value of a function that returns `void` is being used. A `void` return type means the function does not return a meaningful value. Using the result of such a function call is a mistake because the value will always be `null`, and the code likely contains a logic error where the wrong function is being called or the return value is being mistakenly assigned.

## How to fix it

If the function should return a value, change its return type:

```diff-php
 <?php declare(strict_types = 1);

-function doSomething(): void
+function doSomething(): string
 {
-    echo 'done';
+    return 'done';
 }

 $result = doSomething();
```

If the function correctly returns `void`, do not use its return value:

```diff-php
 <?php declare(strict_types = 1);

 function doSomething(): void
 {
     echo 'done';
 }

-$result = doSomething();
+doSomething();
```
