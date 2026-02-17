---
title: "staticMethod.void"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Logger
{
    public static function log(string $message): void
    {
        echo $message;
    }
}

$result = Logger::log('hello');
```

## Why is it reported?

The return value of a static method that returns `void` is being used. A `void` return type means the method does not return a meaningful value. Attempting to use this non-existent return value is a logic error -- the variable will always contain `null`.

## How to fix it

Do not use the return value of a void method. Call it as a standalone statement instead:

```diff-php
 <?php declare(strict_types = 1);

-$result = Logger::log('hello');
+Logger::log('hello');
```

If a return value is needed, change the method to return a meaningful value:

```diff-php
 <?php declare(strict_types = 1);

 class Logger
 {
-    public static function log(string $message): void
+    public static function log(string $message): bool
     {
         echo $message;
+        return true;
     }
 }

 $result = Logger::log('hello');
```
