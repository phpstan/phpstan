---
title: "attribute.nonRepeatable"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

#[\Attribute]
class MyAttribute
{
}

#[MyAttribute]
#[MyAttribute]
class Foo
{
}
```

## Why is it reported?

PHP attributes are not repeatable by default. When an attribute class is declared without the `Attribute::IS_REPEATABLE` flag, it can only be applied once to a given target (class, method, property, etc.). Applying the same non-repeatable attribute more than once to the same target is a runtime error.

In the example above, `MyAttribute` is applied twice to the class `Foo`, but the attribute class does not include the `Attribute::IS_REPEATABLE` flag in its `#[\Attribute]` declaration.

## How to fix it

Remove the duplicate attribute:

```diff-php
 <?php declare(strict_types = 1);

 #[\Attribute]
 class MyAttribute
 {
 }

 #[MyAttribute]
-#[MyAttribute]
 class Foo
 {
 }
```

Or make the attribute repeatable by adding the `IS_REPEATABLE` flag:

```diff-php
 <?php declare(strict_types = 1);

-#[\Attribute]
+#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_CLASS)]
 class MyAttribute
 {
 }

 #[MyAttribute]
 #[MyAttribute]
 class Foo
 {
 }
```
