---
title: "argument.unpackNonIterable"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $value): void
{
    sprintf('%s %s', ...$value);
}
```

## Why is it reported?

The argument unpacking operator `...` can only be used with iterable values (arrays or objects implementing `Traversable`). Attempting to unpack a non-iterable value like an `int`, `string`, or `object` that does not implement `Traversable` is a runtime error.

## How to fix it

Pass an iterable value to the unpacking operator:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(int $value): void
+function doFoo(array $values): void
 {
-    sprintf('%s %s', ...$value);
+    sprintf('%s %s', ...$values);
 }
```

Or pass the arguments directly without unpacking:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $value): void
 {
-    sprintf('%s %s', ...$value);
+    sprintf('%s', $value);
 }
```
