---
title: "attribute.target"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class MyPropertyAttribute
{
}

#[MyPropertyAttribute]
class Foo
{
}
```

## Why is it reported?

PHP attributes declare which targets they can be applied to via the `Attribute::TARGET_*` flags. In the example above, `MyPropertyAttribute` is declared with `TARGET_PROPERTY`, meaning it can only be placed on class properties. Applying it to a class declaration is invalid and PHP will throw an `Error` at runtime when the attribute is instantiated via reflection.

## How to fix it

Move the attribute to a valid target:

```diff-php
 <?php declare(strict_types = 1);

 #[\Attribute(\Attribute::TARGET_PROPERTY)]
 class MyPropertyAttribute
 {
 }

-#[MyPropertyAttribute]
 class Foo
 {
+	#[MyPropertyAttribute]
+	public string $name;
 }
```

Or expand the allowed targets of the attribute class:

```diff-php
 <?php declare(strict_types = 1);

-#[\Attribute(\Attribute::TARGET_PROPERTY)]
+#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_CLASS)]
 class MyPropertyAttribute
 {
 }

 #[MyPropertyAttribute]
 class Foo
 {
 }
```
