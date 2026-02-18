---
title: "generics.lessTypes"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @template TKey
 * @template TValue
 */
class Map
{
}

/**
 * @param Map<string> $map
 */
function doFoo(Map $map): void
{
}
```

## Why is it reported?

A generic type is used without specifying all of its required template type parameters. The class `Map` expects two type arguments (`TKey` and `TValue`), but only one (`string`) is provided. All required template types must be specified for PHPStan to properly analyse the code.

## How to fix it

Specify all required template type arguments:

```diff-php
 /**
- * @param Map<string> $map
+ * @param Map<string, int> $map
  */
 function doFoo(Map $map): void
 {
 }
```
