---
title: "generics.templateDefaultOutOfBounds"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @template T of int
 * @template U of string = int
 */
class Container
{
}
```

## Why is it reported?

The default type specified for a `@template` tag is not a subtype of the template's bound. In the example above, `U` is bounded by `string` (via `of string`), but the default is `int`, which is not a subtype of `string`.

## How to fix it

Change the default type so that it satisfies the bound constraint:

```diff-php
 <?php declare(strict_types = 1);

 /**
  * @template T of int
- * @template U of string = int
+ * @template U of string = string
  */
 class Container
 {
 }
```

Alternatively, change the bound to accommodate the desired default:

```diff-php
 <?php declare(strict_types = 1);

 /**
  * @template T of int
- * @template U of string = int
+ * @template U of string|int = int
  */
 class Container
 {
 }
```
