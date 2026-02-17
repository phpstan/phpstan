---
title: "trait.nameCase"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

trait MyTrait
{
    public function hello(): string
    {
        return 'hello';
    }
}

class Foo
{
    use mytrait;
}
```

## Why is it reported?

The trait is referenced with incorrect letter casing. While PHP class and trait names are case-insensitive at runtime, using inconsistent casing is considered poor practice and can lead to confusion, especially when working with autoloaders that rely on file naming conventions matching class names.

In the example above, the trait is defined as `MyTrait` but referenced as `mytrait`.

## How to fix it

Use the correct casing that matches the trait definition:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-    use mytrait;
+    use MyTrait;
 }
```
