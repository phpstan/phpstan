---
title: "selfOut.type"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Collection
{
    /**
     * @phpstan-self-out int
     */
    public function reset(): void
    {
    }
}
```

## Why is it reported?

The type specified in the `@phpstan-self-out` PHPDoc tag is not a subtype of the class that declares the method. The `@phpstan-self-out` tag narrows the type of `$this` after a method call, so the declared type must be compatible with (a subtype of) the declaring class.

In the example above, `int` is not a subtype of `Collection`.

## How to fix it

Change the `@phpstan-self-out` type to a valid subtype of the declaring class:

```diff-php
 <?php declare(strict_types = 1);

+/**
+ * @template T
+ */
 class Collection
 {
     /**
-     * @phpstan-self-out int
+     * @phpstan-self-out self<int>
      */
     public function reset(): void
     {
     }
 }
```
