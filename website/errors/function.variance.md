---
title: "function.variance"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @template-covariant T
 */
function doFoo(): void
{
}
```

## Why is it reported?

A variance annotation (`@template-covariant` or `@template-contravariant`) was used on a template type parameter of a function or method. Variance annotations are only meaningful on class and interface type parameters, where they describe how subtyping of the container relates to subtyping of the type parameter. Functions and methods do not have this relationship.

## How to fix it

Remove the variance annotation from the function's template type:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @template-covariant T
+ * @template T
  */
 function doFoo(): void
 {
 }
```
