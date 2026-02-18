---
title: "generics.callSiteVarianceRedundant"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @template-covariant T
 */
interface Collection
{
}

/**
 * @param Collection<covariant string> $collection
 */
function doFoo(Collection $collection): void
{
}
```

## Why is it reported?

The call-site variance annotation (e.g. `covariant` or `contravariant`) on a generic type argument is redundant because the template type on the class is already declared with the same variance. In the example, `Collection` already declares `T` as `@template-covariant`, so writing `Collection<covariant string>` is unnecessary -- `Collection<string>` has the same meaning.

## How to fix it

Remove the redundant call-site variance annotation:

```diff-php
 /**
- * @param Collection<covariant string> $collection
+ * @param Collection<string> $collection
  */
 function doFoo(Collection $collection): void
 {
 }
```
