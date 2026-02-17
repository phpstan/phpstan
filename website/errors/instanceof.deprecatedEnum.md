---
title: "instanceof.deprecatedEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewStatus instead */
enum OldStatus
{
    case Active;
    case Inactive;
}

function checkStatus(object $obj): void
{
    if ($obj instanceof OldStatus) { // error
        // ...
    }
}
```

## Why is it reported?

This error is reported by `phpstan/phpstan-deprecation-rules`.

The `instanceof` check references an enum that has been marked with the `@deprecated` PHPDoc tag. Deprecated enums are scheduled for removal or replacement, and any usage -- including in `instanceof` checks -- should be migrated to the recommended alternative. When the deprecated enum is removed, this `instanceof` check will stop working correctly.

## How to fix it

Replace the deprecated enum with its suggested replacement:

```diff-php
 <?php declare(strict_types = 1);

 function checkStatus(object $obj): void
 {
-    if ($obj instanceof OldStatus) {
+    if ($obj instanceof NewStatus) {
         // ...
     }
 }
```
