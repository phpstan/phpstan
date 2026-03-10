---
title: "function.duplicate"
shortDescription: "Function is declared multiple times."
ignorable: true
---

## Code example

This error is reported when the same function name is declared in multiple files within the analysed codebase:

```php
// file1.php
<?php declare(strict_types = 1);

namespace App;

function helper(): void
{
}
```

```php
// file2.php
<?php declare(strict_types = 1);

namespace App;

function helper(): void
{
}
```

## Why is it reported?

The same function name is declared multiple times within the registered [stub files](/user-guide/stub-files).

## How to fix it

Remove the duplicate function declaration, keeping only one:

```diff-php
 <?php declare(strict_types = 1);

 // file2.php
 namespace App;

-function helper(): void
-{
-}
```

If both declarations are intentionally different, rename one of them:

```diff-php
 <?php declare(strict_types = 1);

 // file2.php
 namespace App;

-function helper(): void
+function helperAlternative(): void
 {
 }
```
