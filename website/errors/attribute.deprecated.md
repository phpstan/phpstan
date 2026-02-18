---
title: "attribute.deprecated"
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

An attribute references a class that has been marked as deprecated. Using deprecated attribute classes means the code relies on attributes that are planned for removal in a future version.

In the example above, the `#[OldAttribute]` attribute uses the `OldAttribute` class which is marked as `@deprecated`.

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
