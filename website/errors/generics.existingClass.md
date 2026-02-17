---
title: "generics.existingClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Item
{
}

/**
 * @template Item
 */
class Collection
{
}
```

## Why is it reported?

A `@template` tag uses a name that conflicts with an existing class. In the example above, the template type parameter is named `Item`, but a class with the same name already exists. This creates ambiguity because PHPStan cannot distinguish between the template type parameter and the class when the name is used in type annotations.

## How to fix it

Rename the template type parameter to a name that does not conflict with any existing class:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @template Item
+ * @template TItem
  */
 class Collection
 {
 }
```

A common convention is to prefix template type names with `T` to avoid conflicts with existing classes.
