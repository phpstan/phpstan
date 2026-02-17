---
title: "generics.notSupportedBound"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @template T of resource
 */
class ResourceWrapper
{
}
```

## Why is it reported?

The `@template` tag specifies a bound type that is not supported as a generic type constraint. PHPStan supports the following types as template bounds: `mixed`, `object`, class/interface types (including generic classes), `array`, `string`, `int`, `float`, `bool`, `null`, union types, intersection types, `iterable`, `key-of`, `object-shape`, and other template types.

Types like `resource`, `callable`, `void`, and `never` are not supported as template bounds.

## How to fix it

Use a supported type as the bound. If the template type does not need a specific bound, omit the `of` clause entirely:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @template T of resource
+ * @template T
  */
 class ResourceWrapper
 {
 }
```

Or use a more appropriate supported bound type:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @template T of resource
+ * @template T of object
  */
 class ResourceWrapper
 {
 }
```
