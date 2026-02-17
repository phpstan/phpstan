---
title: "assign.propertyType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
    public int $count;

    public function setCount(string $value): void
    {
        $this->count = $value;
    }
}
```

## Why is it reported?

The value being assigned to a property does not match the property's declared type. In the example above, the property `$count` is declared as `int`, but a `string` value is being assigned to it. This would cause a `TypeError` at runtime when strict types are enabled, or an unexpected implicit type coercion otherwise.

## How to fix it

Ensure the assigned value matches the property's type:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
     public int $count;

-    public function setCount(string $value): void
+    public function setCount(int $value): void
     {
         $this->count = $value;
     }
 }
```

Or convert the value to the correct type before assignment:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
     public int $count;

     public function setCount(string $value): void
     {
-        $this->count = $value;
+        $this->count = (int) $value;
     }
 }
```
