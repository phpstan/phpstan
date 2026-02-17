---
title: "attribute.class"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use Attribute;

#[Attribute]
interface MyAttribute
{
}
```

## Why is it reported?

Only classes can be used as PHP attribute classes. Interfaces, traits, and enums cannot be marked with the `#[Attribute]` attribute. PHP requires attribute classes to be non-abstract classes that can be instantiated.

In the example above, `MyAttribute` is an interface, which cannot serve as an attribute class.

## How to fix it

Change the type declaration to a class:

```diff-php
 <?php declare(strict_types = 1);

 use Attribute;

 #[Attribute]
-interface MyAttribute
+class MyAttribute
 {
 }
```
