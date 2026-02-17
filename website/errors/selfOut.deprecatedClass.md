---
title: "selfOut.deprecatedClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewClass instead */
class DeprecatedClass {}

class Collection
{
    /**
     * @phpstan-self-out self<DeprecatedClass>
     */
    public function filterDeprecated(): void
    {
        // ...
    }
}
```

## Why is it reported?

The `@phpstan-self-out` PHPDoc tag references a class that has been marked as `@deprecated`. The `@phpstan-self-out` tag is used to narrow the type of `$this` after a method call. Referencing a deprecated class in this tag creates a dependency on a symbol that is scheduled for removal or replacement.

## How to fix it

Replace the deprecated class with its non-deprecated replacement in the `@phpstan-self-out` tag:

```diff-php
 <?php declare(strict_types = 1);

 class Collection
 {
     /**
-     * @phpstan-self-out self<DeprecatedClass>
+     * @phpstan-self-out self<NewClass>
      */
-    public function filterDeprecated(): void
+    public function filterNew(): void
     {
         // ...
     }
 }
```

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.
