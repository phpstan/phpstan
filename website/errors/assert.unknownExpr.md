---
title: "assert.unknownExpr"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
    /**
     * @phpstan-assert string $this->barProp
     */
    public function doBar(mixed $a): bool
    {
        return is_string($a);
    }
}
```

## Why is it reported?

The `@phpstan-assert` PHPDoc tag references a property or expression that does not exist on the class. PHPStan cannot verify the assertion because the target expression cannot be resolved. In the example above, `$this->barProp` does not exist on the class `Foo`.

## How to fix it

Reference an existing property or parameter in the assert tag:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
+    public mixed $barProp;
+
     /**
      * @phpstan-assert string $this->barProp
      */
     public function doBar(mixed $a): bool
     {
         return is_string($a);
     }
 }
```

Or fix the assert tag to reference the correct expression:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
     /**
-     * @phpstan-assert string $this->barProp
+     * @phpstan-assert string $a
      */
     public function doBar(mixed $a): bool
     {
         return is_string($a);
     }
 }
```
