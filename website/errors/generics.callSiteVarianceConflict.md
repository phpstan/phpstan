---
title: "generics.callSiteVarianceConflict"
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
    /** @return T */
    public function first(): mixed;
}

/**
 * @param Collection<contravariant string> $collection
 */
function process(Collection $collection): void
{
}
```

## Why is it reported?

A call-site variance annotation (such as `covariant` or `contravariant`) on a generic type argument conflicts with the variance declared on the template type parameter of the class or interface. In this example, the template parameter `T` of `Collection` is declared as `@template-covariant`, but the call-site annotation uses `contravariant`, which is not a valid position for a covariant template type.

Call-site variance annotations (also called "use-site variance" or "type projections") must be compatible with the declaration-site variance of the template parameter.

## How to fix it

Remove the conflicting call-site variance annotation, or use one that is compatible with the declaration-site variance:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @param Collection<contravariant string> $collection
+ * @param Collection<covariant string> $collection
  */
 function process(Collection $collection): void
 {
 }
```

Or simply use the type without a call-site variance annotation:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @param Collection<contravariant string> $collection
+ * @param Collection<string> $collection
  */
 function process(Collection $collection): void
 {
 }
```
