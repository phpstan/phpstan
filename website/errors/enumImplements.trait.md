---
title: "enumImplements.trait"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

trait FooTrait
{
}

enum MyEnum implements FooTrait
{
	case A;
	case B;
}
```

## Why is it reported?

An enum's `implements` clause must list interfaces, not traits. Traits are used with the `use` keyword inside the enum body, not in the `implements` clause. This is a PHP language-level error: the `implements` keyword is reserved for interfaces.

## How to fix it

If the intent is to use the trait in the enum, move it to a `use` statement inside the enum body:

```diff-php
 <?php declare(strict_types = 1);

 trait FooTrait
 {
 }

-enum MyEnum implements FooTrait
+enum MyEnum
 {
+	use FooTrait;
+
 	case A;
 	case B;
 }
```

If the intent is to implement an interface, replace the trait reference with the correct interface:

```diff-php
 <?php declare(strict_types = 1);

-trait FooTrait
+interface FooInterface
 {
 }

-enum MyEnum implements FooTrait
+enum MyEnum implements FooInterface
 {
 	case A;
 	case B;
 }
```
