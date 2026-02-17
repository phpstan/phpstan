---
title: "class.noParent"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
    public function bar(): void
    {
        parent::bar(); // reported: Foo::bar() calls parent::bar() but Foo does not extend any class.
    }
}
```

## Why is it reported?

The `parent` keyword is used inside a class that does not extend any other class. The `parent` keyword refers to the parent class, so using it in a class without a parent has no valid target and results in a fatal error at runtime.

## How to fix it

Either extend a parent class that provides the referenced member, or remove the `parent::` call.

Adding a parent class:

```diff-php
-class Foo
+class Foo extends BaseClass
 {
     public function bar(): void
     {
         parent::bar();
     }
 }
```

Or removing the call:

```diff-php
 class Foo
 {
     public function bar(): void
     {
-        parent::bar();
     }
 }
```
