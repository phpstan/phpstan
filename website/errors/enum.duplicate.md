---
title: "enum.duplicate"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// file1.php
namespace App;

enum Status
{
    case Active;
    case Inactive;
}

// file2.php
namespace App;

enum Status
{
    case Pending;
    case Completed;
}
```

## Why is it reported?

The same enum name is declared multiple times within the same namespace across different files. PHP does not allow two types with the same fully qualified name. When an enum is declared more than once, PHP will throw a fatal error at runtime. This usually happens due to copy-paste mistakes, file inclusion issues, or when two files define the same enum and are both included in the project.

## How to fix it

Remove the duplicate enum declaration, keeping only one:

```diff-php
 <?php declare(strict_types = 1);

 // file2.php
 namespace App;

-enum Status
-{
-    case Pending;
-    case Completed;
-}
```

If both declarations are intentionally different, rename one of them:

```diff-php
 <?php declare(strict_types = 1);

 // file2.php
 namespace App;

-enum Status
+enum TaskStatus
 {
     case Pending;
     case Completed;
 }
```
