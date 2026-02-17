---
title: "generics.notSubtype"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @template T
 * @param T $object
 * @return T
 */
function wrap(object $object)
{
    return $object;
}

/** @var int $result */
$result = wrap(new \stdClass());
```

## Why is it reported?

A type argument provided to a generic type is not a subtype of the corresponding template type's bound. In the example above, the function `wrap` declares a template type `T` without an explicit bound, but the parameter `$object` has a native `object` type. Since `@template T` without a bound defaults to `mixed`, but the parameter restricts it to `object`, the template type needs to declare `@template T of object` to match the native type constraint.

This ensures that generic types are used with compatible type arguments that satisfy the declared constraints.

## How to fix it

Add the appropriate bound to the template type to match the native type of the parameter:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @template T
+ * @template T of object
  * @param T $object
  * @return T
  */
 function wrap(object $object)
 {
     return $object;
 }
```

This way `T` is properly constrained to `object`, matching the native parameter type.
