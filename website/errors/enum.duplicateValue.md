---
title: "enum.duplicateValue"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

enum Priority: int
{
    case Low = 1;
    case Medium = 2;
    case High = 2;
    case Critical = 1;
}
```

## Why is it reported?

A backed enum has multiple cases with the same backing value. Each case in a backed enum must have a unique value. PHP enforces this constraint and will throw a fatal error at runtime if two cases share the same value. In this example, `Low` and `Critical` both have value `1`, and `Medium` and `High` both have value `2`.

## How to fix it

Assign unique values to each enum case:

```diff-php
 <?php declare(strict_types = 1);

 enum Priority: int
 {
     case Low = 1;
     case Medium = 2;
-    case High = 2;
-    case Critical = 1;
+    case High = 3;
+    case Critical = 4;
 }
```
