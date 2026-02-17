---
title: "generics.notSubtype"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @template T of \Exception
 */
class Container
{
    /** @var T */
    public $value;
}

/** @var Container<\stdClass> $container */
$container = new Container();
```

## Why is it reported?

A type argument provided to a generic type is not a subtype of the corresponding template type's bound. In the example above, the class `Container` declares a template type `T` with an upper bound of `\Exception` (via `@template T of \Exception`). The type `\stdClass` is used as a type argument for `T`, but `\stdClass` is not a subtype of `\Exception`.

This ensures that generic types are used with compatible type arguments that satisfy the declared constraints.

## How to fix it

Use a type argument that is a subtype of the declared bound:

```diff-php
 <?php declare(strict_types = 1);

-/** @var Container<\stdClass> $container */
+/** @var Container<\RuntimeException> $container */
 $container = new Container();
```

Alternatively, if the bound is too restrictive, widen it to accept the intended type argument:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @template T of \Exception
+ * @template T of object
  */
 class Container
 {
     /** @var T */
     public $value;
 }
```
