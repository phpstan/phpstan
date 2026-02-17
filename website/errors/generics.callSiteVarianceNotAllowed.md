---
title: "generics.callSiteVarianceNotAllowed"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @template T */
interface Collection
{
}

/** @implements Collection<covariant string> */
class StringCollection implements Collection
{
}
```

## Why is it reported?

Call-site variance annotations (`covariant` and `contravariant`) are used in PHPDoc to project type parameters at use sites. However, they are not allowed when specifying type arguments in `@extends`, `@implements`, or `@use` tags. These locations require concrete type arguments, not variance-annotated projections.

In the example above, `covariant string` is used in the `@implements` tag, but call-site variance annotations are not meaningful in this context because the class is providing a concrete type for the template parameter.

## How to fix it

Remove the variance annotation and specify the type directly:

```diff-php
 <?php declare(strict_types = 1);

-/** @implements Collection<covariant string> */
+/** @implements Collection<string> */
 class StringCollection implements Collection
 {
 }
```
