---
title: "assert.deprecatedEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewStatus instead */
enum OldStatus: string
{
    case Active = 'active';
}

class Foo
{
    /**
     * @phpstan-assert OldStatus $value
     */
    public function assertStatus(mixed $value): void
    {
        // ...
    }
}
```

## Why is it reported?

The `@phpstan-assert` PHPDoc tag references an enum that has been marked with a `@deprecated` PHPDoc tag. The enum is scheduled for removal or replacement, and any usage of it -- including in assertion type annotations -- should be migrated to the recommended alternative.

This rule is provided by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) package.

## How to fix it

Replace the deprecated enum with its recommended replacement in the `@phpstan-assert` tag:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
     /**
-     * @phpstan-assert OldStatus $value
+     * @phpstan-assert NewStatus $value
      */
     public function assertStatus(mixed $value): void
     {
         // ...
     }
 }
```
