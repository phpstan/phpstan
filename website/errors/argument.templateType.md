---
title: "argument.templateType"
shortDescription: "Template type cannot be resolved from the provided arguments."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @template T of int
 * @param T $value
 * @return T
 */
function identity($value)
{
	return $value;
}

$result = identity('hello');
```

## Why is it reported?

The called function or method declares a template type (generic type parameter) with a bound, but the provided argument does not satisfy the bound. PHPStan cannot resolve the template type because the argument type falls outside the allowed range specified by the `@template T of ...` constraint.

In the example above, the template type `T` is bounded to `int`, but a `string` is passed. PHPStan cannot resolve `T` because `string` is not a subtype of `int`.

Learn more: [Solving PHPStan error "Unable to resolve template type"](/blog/solving-phpstan-error-unable-to-resolve-template-type)

## How to fix it

Pass an argument that satisfies the template bound:

```diff-php
 <?php declare(strict_types = 1);

-$result = identity('hello');
+$result = identity(42);
```

Or widen the template bound if more types should be accepted:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @template T of int
+ * @template T of int|string
  * @param T $value
  * @return T
  */
 function identity($value)
 {
 	return $value;
 }
```
