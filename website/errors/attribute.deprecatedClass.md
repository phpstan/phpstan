---
title: "attribute.deprecatedClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewAttribute instead */
#[\Attribute]
class OldAttribute
{
}

#[OldAttribute]
class Foo
{
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

An attribute references a class that has been marked as `@deprecated`. Using deprecated attribute classes means your code relies on attributes that are planned for removal.

In the example above, the `#[OldAttribute]` attribute uses the deprecated `OldAttribute` class.

## How to fix it

Replace the deprecated attribute with its recommended replacement:

```diff-php
 <?php declare(strict_types = 1);

-#[OldAttribute]
+#[NewAttribute]
 class Foo
 {
 }
```
