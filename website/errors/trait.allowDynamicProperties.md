---
title: "trait.allowDynamicProperties"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

#[\AllowDynamicProperties]
trait DynamicTrait
{
}
```

## Why is it reported?

The `#[\AllowDynamicProperties]` attribute cannot be used with traits. Traits are not instantiated directly and do not own properties in the same way classes do. The attribute only has meaning when applied to a class. PHP will emit a fatal error at runtime when this attribute is used on a trait.

## How to fix it

Remove the attribute from the trait and apply it to the classes that use the trait instead:

```diff-php
 <?php declare(strict_types = 1);

-#[\AllowDynamicProperties]
 trait DynamicTrait
 {
 }

+#[\AllowDynamicProperties]
 class Foo
 {
     use DynamicTrait;
 }
```
