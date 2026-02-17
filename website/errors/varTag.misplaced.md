---
title: "varTag.misplaced"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @var int $x */
class Foo
{
}
```

## Why is it reported?

The `@var` PHPDoc tag is placed above a class, interface, enum, function, or method definition where it has no effect. The `@var` tag is designed to annotate the type of a variable in a local scope, typically above a variable assignment or a `foreach` loop. Placing it above a structural element like a class or method does nothing because there is no variable to annotate.

Other locations where this is reported:

```php
<?php declare(strict_types = 1);

/** @var string $name */
function doFoo(): void
{
    // @var tag above a function has no effect
}

class Bar
{
    /** @var int $count */
    public function doBar(): void
    {
        // @var tag above a method has no effect
    }
}
```

## How to fix it

Move the `@var` tag to the correct location, directly above a variable assignment:

```diff-php
 <?php declare(strict_types = 1);

-/** @var int $x */
 class Foo
 {
+    public function doFoo(): void
+    {
+        /** @var int $x */
+        $x = $this->getValue();
+    }
 }
```

If the intent was to document a class property, use the `@var` tag on the property declaration itself:

```diff-php
 <?php declare(strict_types = 1);

-/** @var int $x */
 class Foo
 {
+    /** @var int */
+    public $x;
 }
```

Or use a native PHP type declaration:

```diff-php
 <?php declare(strict_types = 1);

-/** @var int $x */
 class Foo
 {
+    public int $x;
 }
```
