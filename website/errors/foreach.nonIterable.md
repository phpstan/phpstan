---
title: "foreach.nonIterable"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function process(string $data): void
{
    foreach ($data as $char) {
        echo $char;
    }
}
```

## Why is it reported?

The expression passed to `foreach` is not an iterable type. PHP's `foreach` only works with arrays, objects implementing `Traversable`, or other iterable types. Passing a non-iterable value will produce a runtime warning.

## How to fix it

Convert the value to an iterable type, or ensure the variable has the correct type:

```diff-php
 <?php declare(strict_types = 1);

-function process(string $data): void
+function process(string $data): void
 {
-    foreach ($data as $char) {
+    foreach (str_split($data) as $char) {
         echo $char;
     }
 }
```

Or fix the parameter type if it should be an array:

```diff-php
 <?php declare(strict_types = 1);

-function process(string $data): void
+/** @param list<string> $data */
+function process(array $data): void
 {
     foreach ($data as $char) {
         echo $char;
     }
 }
```
