---
title: "enum.caseWithValue"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

enum Color
{
    case Red = 1;
    case Green = 2;
    case Blue = 3;
}
```

## Why is it reported?

The enum is a pure (non-backed) enum, meaning it has no scalar type declaration (like `: int` or `: string`). Pure enums cannot have values assigned to their cases. Only backed enums (declared with `: int` or `: string`) can have values. This is a PHP language constraint.

## How to fix it

If the cases need values, declare the enum as a backed enum:

```diff-php
 <?php declare(strict_types = 1);

-enum Color
+enum Color: int
 {
     case Red = 1;
     case Green = 2;
     case Blue = 3;
 }
```

If the enum does not need values, remove them from the cases:

```diff-php
 <?php declare(strict_types = 1);

 enum Color
 {
-    case Red = 1;
-    case Green = 2;
-    case Blue = 3;
+    case Red;
+    case Green;
+    case Blue;
 }
```
