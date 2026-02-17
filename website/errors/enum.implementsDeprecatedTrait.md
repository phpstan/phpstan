---
title: "enum.implementsDeprecatedTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewHelper instead */
trait OldHelper
{
    public function help(): void
    {
        // ...
    }
}

enum Status implements OldHelper // error
{
    case Active;
    case Inactive;
}
```

## Why is it reported?

This error is reported by `phpstan/phpstan-deprecation-rules`.

The enum references a deprecated trait in its `implements` clause. The trait has been marked with the `@deprecated` PHPDoc tag, indicating it is scheduled for removal or replacement. Additionally, traits cannot appear in an `implements` clause -- only interfaces can be implemented.

## How to fix it

Replace the deprecated trait with a non-deprecated interface:

```diff-php
 <?php declare(strict_types = 1);

-enum Status implements OldHelper
+enum Status implements NewHelperInterface
 {
     case Active;
     case Inactive;
 }
```

If the trait functionality is needed, use it with the `use` keyword inside the enum body instead of `implements`:

```diff-php
 <?php declare(strict_types = 1);

-enum Status implements OldHelper
+enum Status
 {
+    use NewHelper;
+
     case Active;
     case Inactive;
 }
```
