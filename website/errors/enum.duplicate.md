---
title: "enum.duplicate"
shortDescription: "Enum with the same name is declared multiple times."
ignorable: true
---

## Code example

This error is reported when the same enum name is declared in multiple files within the analysed codebase:

```php
// file1.php
<?php declare(strict_types = 1);

namespace App;

enum Status
{
    case Active;
    case Inactive;
}
```

```php
// file2.php
<?php declare(strict_types = 1);

namespace App;

enum Status
{
    case Pending;
    case Completed;
}
```

## Why is it reported?

The same enum name is declared multiple times within the registered [stub files](/user-guide/stub-files).

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
