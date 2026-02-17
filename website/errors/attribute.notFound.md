---
title: "attribute.notFound"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

#[MyCustomAttribute]
class Foo
{
}
```

## Why is it reported?

The attribute class referenced in the `#[...]` syntax does not exist. PHP attributes must refer to existing classes. Using a non-existent class as an attribute will cause a runtime error when the attribute is reflected upon.

In the example above, the class `MyCustomAttribute` is not defined and cannot be autoloaded, so the attribute cannot be resolved.

## How to fix it

Define the attribute class or fix the class name if it was misspelled:

```diff-php
 <?php declare(strict_types = 1);

+#[\Attribute]
+class MyCustomAttribute
+{
+}
+
 #[MyCustomAttribute]
 class Foo
 {
 }
```

Or correct the class name if the attribute exists under a different name or namespace:

```diff-php
 <?php declare(strict_types = 1);

-#[MyCustomAttribute]
+#[\App\Attributes\MyCustomAttribute]
 class Foo
 {
 }
```
