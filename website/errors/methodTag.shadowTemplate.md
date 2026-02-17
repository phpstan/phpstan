---
title: "methodTag.shadowTemplate"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @template T
 * @method void process<T>(T $item)
 */
class Container
{
}
```

## Why is it reported?

A `@method` PHPDoc tag defines a template type parameter that has the same name as a template type parameter already defined on the class via `@template`. This shadows the class-level template type, making it ambiguous which `T` is being referred to. The method-level template hides the class-level one within the scope of that method tag.

## How to fix it

Rename the template type parameter in the `@method` tag to avoid the name collision:

```diff-php
 <?php declare(strict_types = 1);

 /**
  * @template T
- * @method void process<T>(T $item)
+ * @method void process<U>(U $item)
  */
 class Container
 {
 }
```

Alternatively, if the method should use the class-level template type, remove the template parameter from the `@method` tag:

```diff-php
 <?php declare(strict_types = 1);

 /**
  * @template T
- * @method void process<T>(T $item)
+ * @method void process(T $item)
  */
 class Container
 {
 }
```
