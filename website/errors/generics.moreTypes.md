---
title: "generics.moreTypes"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @template T
 * @template U of \Exception
 */
class Collection
{
}

/**
 * @extends Collection<int, \InvalidArgumentException, string>
 */
class MyCollection extends Collection
{
}
```

## Why is it reported?

The generic type `Collection<int, \InvalidArgumentException, string>` specifies 3 type arguments, but the `Collection` class only declares 2 template parameters (`T` and `U`). The extra type argument `string` has no corresponding template parameter and will be ignored.

## How to fix it

Remove the extra type arguments to match the number of template parameters:

```diff-php
 /**
- * @extends Collection<int, \InvalidArgumentException, string>
+ * @extends Collection<int, \InvalidArgumentException>
  */
 class MyCollection extends Collection
 {
 }
```
