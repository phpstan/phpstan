---
title: "function.duplicate"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

function helper(): void
{
}

function helper(): void
{
}
```

## Why is it reported?

The same function name is declared multiple times within the same namespace. PHP does not allow two functions with the same name in the same namespace. When a function is declared more than once, PHP will throw a fatal error at runtime. This usually happens due to copy-paste mistakes, file inclusion issues, or when two files define the same function and are both included in the project.

## How to fix it

Remove the duplicate function declaration, keeping only one:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

 function helper(): void
 {
 }

-function helper(): void
-{
-}
```

If both declarations are intentionally different, rename one of them:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

 function helper(): void
 {
 }

-function helper(): void
+function helperAlternative(): void
 {
 }
```
