---
title: "property.unresolvableNativeType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Lorem
{
}

class Ipsum
{
}

class Test
{
	private Lorem&Ipsum $prop; // ERROR: Property Test::$prop has unresolvable native type.
}
```

## Why is it reported?

The property has a native intersection type that cannot be resolved. An intersection type like `Lorem&Ipsum` requires that a value is an instance of both `Lorem` and `Ipsum` simultaneously. When these two classes are unrelated and neither extends the other, no value can satisfy this type, making it unresolvable.

This typically occurs with intersection types (available since PHP 8.1) where the combined type constraints are impossible to satisfy.

## How to fix it

Use interfaces in intersection types instead of unrelated classes, since a single object can implement multiple interfaces:

```diff-php
 <?php declare(strict_types = 1);

-class Lorem
+interface Loremable
 {
 }

-class Ipsum
+interface Ipsumable
 {
 }

 class Test
 {
-	private Lorem&Ipsum $prop;
+	private Loremable&Ipsumable $prop;
 }
```

Alternatively, if only one of the types is needed, simplify the type:

```diff-php
 <?php declare(strict_types = 1);

 class Test
 {
-	private Lorem&Ipsum $prop;
+	private Lorem $prop;
 }
```
