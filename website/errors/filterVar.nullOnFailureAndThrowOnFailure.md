---
title: "filterVar.nullOnFailureAndThrowOnFailure"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$value = filter_var(
    'test',
    FILTER_VALIDATE_INT,
    FILTER_NULL_ON_FAILURE | FILTER_THROW_ON_FAILURE,
);
```

## Why is it reported?

The flags `FILTER_NULL_ON_FAILURE` and `FILTER_THROW_ON_FAILURE` are mutually exclusive. `FILTER_NULL_ON_FAILURE` causes `filter_var()` to return `null` on failure, while `FILTER_THROW_ON_FAILURE` causes it to throw an exception. Using both at the same time produces contradictory behaviour.

## How to fix it

Use only one of the two flags depending on the desired failure handling:

To return `null` on failure:

```diff-php
 <?php declare(strict_types = 1);

 $value = filter_var(
     'test',
     FILTER_VALIDATE_INT,
-    FILTER_NULL_ON_FAILURE | FILTER_THROW_ON_FAILURE,
+    FILTER_NULL_ON_FAILURE,
 );
```

To throw an exception on failure:

```diff-php
 <?php declare(strict_types = 1);

 $value = filter_var(
     'test',
     FILTER_VALIDATE_INT,
-    FILTER_NULL_ON_FAILURE | FILTER_THROW_ON_FAILURE,
+    FILTER_THROW_ON_FAILURE,
 );
```
