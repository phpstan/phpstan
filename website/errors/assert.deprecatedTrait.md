---
title: "assert.deprecatedTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewHelper instead */
trait OldHelper
{
}

class Foo
{
    /**
     * @phpstan-assert OldHelper $value
     */
    public function assertHelper(mixed $value): void
    {
        // ...
    }
}
```

## Why is it reported?

The `@phpstan-assert` PHPDoc tag references a trait that has been marked with a `@deprecated` PHPDoc tag. The trait is scheduled for removal or replacement, and any usage of it -- including in assertion type annotations -- should be migrated to the recommended alternative.

This rule is provided by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) package.

## How to fix it

Replace the deprecated trait with its recommended replacement in the `@phpstan-assert` tag:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
     /**
-     * @phpstan-assert OldHelper $value
+     * @phpstan-assert NewHelper $value
      */
     public function assertHelper(mixed $value): void
     {
         // ...
     }
 }
```
