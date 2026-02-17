---
title: "selfOut.deprecatedInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

/** @deprecated Use NewInterface instead */
interface OldInterface
{
}

class Foo
{
    /**
     * @phpstan-self-out self&OldInterface
     */
    public function apply(): void
    {
    }
}
```

## Why is it reported?

This error is reported by the `phpstan/phpstan-deprecation-rules` package.

The `@phpstan-self-out` PHPDoc tag references an interface that is marked as `@deprecated`. The `@phpstan-self-out` tag is used to narrow the type of `$this` after a method call. Referencing a deprecated interface in this context creates a dependency on a symbol that is scheduled for removal.

## How to fix it

Replace the deprecated interface with its recommended replacement in the `@phpstan-self-out` tag:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

 class Foo
 {
     /**
-     * @phpstan-self-out self&OldInterface
+     * @phpstan-self-out self&NewInterface
      */
     public function apply(): void
     {
     }
 }
```

If the method or class is itself deprecated, the error will not be reported.
