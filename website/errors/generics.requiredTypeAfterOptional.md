---
title: "generics.requiredTypeAfterOptional"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @template T = string
 * @template U
 */
class Container
{
}
```

## Why is it reported?

A required `@template` type parameter appears after an optional one. Template type `T` has a default value (`= string`), making it optional, but the subsequent template type `U` does not have a default value, making it required. Just like function parameters, required template types must come before optional ones.

When a generic type is instantiated, type arguments are assigned in order. If an optional template parameter precedes a required one, it becomes impossible to omit the optional argument while still providing the required one.

## How to fix it

Reorder the template parameters so that all required types come before optional ones:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @template T = string
  * @template U
+ * @template T = string
  */
 class Container
 {
 }
```

Alternatively, give the required template type a default value as well:

```diff-php
 <?php declare(strict_types = 1);

 /**
  * @template T = string
- * @template U
+ * @template U = int
  */
 class Container
 {
 }
```
