---
title: "attribute.noConstructor"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use Attribute;

#[Attribute]
class MyAttribute
{
}

#[MyAttribute('some value')]
class Foo
{
}
```

## Why is it reported?

The attribute class does not have a constructor, but it is being instantiated with parameters. When an attribute class has no constructor, it cannot accept any arguments. Passing arguments to an attribute without a constructor will result in an error.

In the example above, `MyAttribute` has no constructor, so it cannot be used with the argument `'some value'`.

## How to fix it

Add a constructor to the attribute class to accept the parameters:

```diff-php
 <?php declare(strict_types = 1);

 use Attribute;

 #[Attribute]
 class MyAttribute
 {
+    public function __construct(public string $value)
+    {
+    }
 }

 #[MyAttribute('some value')]
 class Foo
 {
 }
```

Or remove the arguments from the attribute usage:

```diff-php
 <?php declare(strict_types = 1);

-#[MyAttribute('some value')]
+#[MyAttribute]
 class Foo
 {
 }
```
