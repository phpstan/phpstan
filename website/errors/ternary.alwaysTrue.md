---
title: "ternary.alwaysTrue"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(\stdClass $std): string
{
    return $std ? 'yes' : 'no';
}
```

## Why is it reported?

The condition of the ternary operator always evaluates to `true`. This means the false branch (`'no'` in the example) is dead code and can never be reached.

In this example, `$std` is typed as `\stdClass` (a non-nullable object), which always evaluates to `true` in a boolean context. The ternary is therefore redundant.

## How to fix it

Remove the ternary and use the true branch value directly:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(\stdClass $std): string
 {
-    return $std ? 'yes' : 'no';
+    return 'yes';
 }
```

If the condition should be nullable, adjust the type to reflect that:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(\stdClass $std): string
+function doFoo(?\stdClass $std): string
 {
     return $std ? 'yes' : 'no';
 }
```
